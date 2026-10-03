<?php

declare(strict_types=1);

namespace App\Services\Bible;

use Illuminate\Support\Facades\Http;

class BibleApiComProvider implements BibleProvider
{
    /**
     * @var array<string, array{0: string, 1: string}>
     */
    private const TRANSLATIONS = [
        'kjv' => ['King James Version', 'KJV'],
        'web' => ['World English Bible', 'WEB'],
        'asv' => ['American Standard Version', 'ASV'],
        'bbe' => ['Bible in Basic English', 'BBE'],
        'darby' => ['Darby Translation', 'DARBY'],
        'ylt' => ["Young's Literal Translation", 'YLT'],
    ];

    public function __construct(
        private readonly string $baseUrl,
    ) {}

    public function name(): string
    {
        return 'bible_api_com';
    }

    public function configured(): bool
    {
        return true;
    }

    public function translations(): array
    {
        $translations = [];
        foreach (self::TRANSLATIONS as $id => [$name, $abbreviation]) {
            $translations[] = new BibleTranslation($id, $name, $abbreviation, 'English', $this->name());
        }

        return $translations;
    }

    public function supports(string $translation): bool
    {
        return isset(self::TRANSLATIONS[strtolower($translation)]);
    }

    public function passage(string $translation, PassageReference $reference): BiblePassage
    {
        $translation = strtolower($translation);
        if (! $this->supports($translation)) {
            throw new TranslationUnavailableException('Modern translations require a configured API.Bible key.');
        }

        $referenceLabel = $reference->label;
        $response = Http::timeout(15)
            ->acceptJson()
            ->get(rtrim($this->baseUrl, '/').'/'.rawurlencode($referenceLabel), [
                'translation' => $translation,
            ]);

        if ($response->failed()) {
            throw new BibleProviderException('bible-api.com request failed.');
        }

        $data = $response->json();
        $text = is_array($data) ? trim((string) ($data['text'] ?? '')) : '';
        if ($text === '') {
            throw new BibleProviderException('bible-api.com returned an empty passage.');
        }

        return new BiblePassage(
            reference: (string) ($data['reference'] ?? $referenceLabel),
            text: $text,
            translation: $translation,
            translationAbbr: self::TRANSLATIONS[$translation][1],
            copyright: $data['translation_note'] ?? null,
            verses: is_array($data['verses'] ?? null) ? $data['verses'] : [],
            provider: $this->name(),
        );
    }
}
