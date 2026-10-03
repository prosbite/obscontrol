<?php

declare(strict_types=1);

namespace App\Services\Bible;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ApiBibleProvider implements BibleProvider
{
    public function __construct(
        private readonly ?string $apiKey,
        private readonly string $baseUrl,
    ) {}

    public function name(): string
    {
        return 'api_bible';
    }

    public function configured(): bool
    {
        return $this->apiKey !== null && $this->apiKey !== '';
    }

    public function translations(): array
    {
        if (! $this->configured()) {
            throw new BibleProviderException('API.Bible is not configured.');
        }

        $cacheKey = 'bible.api_bible.translations';
        $cached = Cache::get($cacheKey);
        if (is_array($cached) && $cached !== []) {
            return $cached;
        }

        $response = Http::timeout(10)
            ->withHeaders($this->headers())
            ->get($this->baseUrl.'/bibles', ['language' => 'eng']);

        if ($response->failed()) {
            throw new BibleProviderException('API.Bible translations request failed.');
        }

        $translations = [];
        foreach ((array) $response->json('data', []) as $bible) {
            if (empty($bible['id'])) {
                continue;
            }

            $translations[] = new BibleTranslation(
                id: (string) $bible['id'],
                name: (string) ($bible['name'] ?? $bible['abbreviation'] ?? $bible['id']),
                abbreviation: $bible['abbreviation'] ?? ($bible['abbrev'] ?? null),
                language: is_array($bible['language'] ?? null)
                    ? ($bible['language']['name'] ?? null)
                    : ($bible['language'] ?? null),
                provider: $this->name(),
            );
        }

        if ($translations === []) {
            throw new BibleProviderException('API.Bible returned no translations.');
        }

        Cache::put(
            $cacheKey,
            $translations,
            now()->addMinutes((int) config('bible.api_bible.translations_cache_minutes', 1440)),
        );

        return $translations;
    }

    public function supports(string $translation): bool
    {
        return $this->configured();
    }

    public function passage(string $translation, PassageReference $reference): BiblePassage
    {
        if (! $this->configured()) {
            throw new BibleProviderException('API.Bible is not configured.');
        }

        $url = $this->baseUrl.'/bibles/'.$translation.'/passages/'.$reference->canonical();

        $response = Http::timeout(15)
            ->withHeaders($this->headers())
            ->get($url, [
                'content-type' => 'text',
                'include-notes' => 'false',
                'include-titles' => 'false',
                'include-chapter-numbers' => 'false',
                'include-verse-numbers' => 'false',
            ]);

        if ($response->failed()) {
            throw new BibleProviderException('API.Bible passage request failed.');
        }

        $data = $response->json('data');
        if (! is_array($data)) {
            throw new BibleProviderException('Unexpected API.Bible response.');
        }

        $text = $this->clean((string) ($data['content'] ?? ''));
        if ($text === '') {
            throw new BibleProviderException('API.Bible returned an empty passage.');
        }

        return new BiblePassage(
            reference: (string) ($data['reference'] ?? $reference->label),
            text: $text,
            translation: $translation,
            translationAbbr: $data['abbreviation'] ?? null,
            copyright: $data['copyright'] ?? null,
            verses: [],
            provider: $this->name(),
        );
    }

    /**
     * @return array<string, string>
     */
    private function headers(): array
    {
        return [
            'api-key' => (string) $this->apiKey,
            'Accept' => 'application/json',
        ];
    }

    private function clean(string $text): string
    {
        $text = strip_tags($text);
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;

        return trim($text);
    }
}
