<?php

declare(strict_types=1);

namespace App\Services\Bible;

use App\Models\BiblePassage as BiblePassageModel;

/**
 * Orchestrates reference parsing, provider calls, caching and fallback.
 */
class BibleService
{
    public function __construct(
        private readonly ReferenceParser $parser,
        private readonly ApiBibleProvider $apiBible,
        private readonly BibleApiComProvider $fallback,
    ) {}

    public function providerName(): string
    {
        if (config('bible.provider', 'api_bible') === 'api_bible' && ! $this->apiBible->configured()) {
            return 'bible_api_com';
        }

        return config('bible.provider', 'api_bible') === 'bible_api_com' ? 'bible_api_com' : 'api_bible';
    }

    public function defaultTranslation(): string
    {
        return $this->providerName() === 'api_bible'
            ? (string) config('bible.api_bible.default_bible_id')
            : (string) config('bible.default_translation', 'kjv');
    }

    /**
     * @return BibleTranslation[]
     */
    public function translations(): array
    {
        if ($this->providerName() === 'api_bible') {
            try {
                return $this->apiBible->translations();
            } catch (BibleException) {
                // Fall through to the public-domain list.
            }
        }

        return $this->fallback->translations();
    }

    /**
     * @throws BibleException
     */
    public function passage(string $input, ?string $translation = null): BiblePassage
    {
        $references = $this->parser->parse($input);
        $canonical = implode(',', array_map(
            static fn (PassageReference $reference): string => $reference->canonical(),
            $references,
        ));

        $providerName = $this->providerName();
        $translation = ($translation !== null && trim($translation) !== '')
            ? trim($translation)
            : $this->defaultTranslation();

        $key = $this->cacheKey($providerName, $translation, $canonical);
        $cached = BiblePassageModel::query()->where('reference_key', $key)->first();

        if ($cached !== null && $this->isFresh($cached)) {
            return $this->toDto($cached);
        }

        try {
            $passage = $this->fetch($providerName, $translation, $references);
        } catch (BibleException $exception) {
            $fallbackPassage = $this->fetchFallback($providerName, $translation, $references);

            if ($fallbackPassage !== null) {
                $this->store(
                    $this->cacheKey(
                        $fallbackPassage->provider ?? 'bible_api_com',
                        $fallbackPassage->translation ?? $translation,
                        $canonical,
                    ),
                    $fallbackPassage,
                );

                return $fallbackPassage;
            }

            if ($cached !== null && $cached->fetched_at !== null && $cached->fetched_at->gt(now()->subDays(30))) {
                return $this->toDto($cached);
            }

            throw $exception;
        }

        $this->store($key, $passage);

        return $passage;
    }

    /**
     * @param  PassageReference[]  $references
     */
    private function fetch(string $providerName, string $translation, array $references): BiblePassage
    {
        $provider = $providerName === 'api_bible' ? $this->apiBible : $this->fallback;

        $texts = [];
        $abbreviation = null;
        $copyright = null;
        $verses = [];
        $labels = [];

        foreach ($references as $reference) {
            $passage = $provider->passage($translation, $reference);

            if (trim($passage->text) !== '') {
                $texts[] = trim($passage->text);
            }

            $abbreviation ??= $passage->translationAbbr;
            $copyright ??= $passage->copyright;

            if ($passage->verses !== []) {
                $verses = array_merge($verses, $passage->verses);
            }

            $labels[] = $passage->reference !== '' ? $passage->reference : $reference->label;
        }

        if ($texts === []) {
            throw new BibleProviderException('The provider returned no text.');
        }

        return new BiblePassage(
            reference: implode('; ', $labels),
            text: implode("\n\n", $texts),
            translation: $translation,
            translationAbbr: $abbreviation,
            copyright: $copyright,
            verses: $verses,
            provider: $providerName,
        );
    }

    /**
     * @param  PassageReference[]  $references
     */
    private function fetchFallback(string $primaryName, string $translation, array $references): ?BiblePassage
    {
        if ($primaryName !== 'api_bible' || ! $this->fallback->supports($translation)) {
            return null;
        }

        try {
            return $this->fetch('bible_api_com', $translation, $references);
        } catch (BibleException) {
            return null;
        }
    }

    private function cacheKey(string $provider, string $translation, string $canonical): string
    {
        return $provider.':'.$translation.':'.$canonical;
    }

    private function isFresh(BiblePassageModel $model): bool
    {
        if ($model->fetched_at === null) {
            return false;
        }

        $ttl = min((int) config('bible.cache_ttl_days', 7), 30);

        return $model->fetched_at->gt(now()->subDays($ttl));
    }

    private function store(string $key, BiblePassage $passage): void
    {
        BiblePassageModel::query()->updateOrCreate(
            ['reference_key' => $key],
            [
                'provider' => $passage->provider ?? 'unknown',
                'translation' => $passage->translation ?? '',
                'label' => $passage->reference,
                'text' => $passage->text,
                'translation_abbr' => $passage->translationAbbr,
                'copyright' => $passage->copyright,
                'verses' => $passage->verses !== [] ? $passage->verses : null,
                'fetched_at' => now(),
            ],
        );
    }

    private function toDto(BiblePassageModel $model): BiblePassage
    {
        return new BiblePassage(
            reference: $model->label,
            text: $model->text,
            translation: $model->translation,
            translationAbbr: $model->translation_abbr,
            copyright: $model->copyright,
            verses: $model->verses ?? [],
            provider: $model->provider,
        );
    }
}
