<?php

declare(strict_types=1);

namespace App\Services\Bible;

final readonly class BiblePassage
{
    /**
     * @param  array<int, array<string, mixed>>  $verses
     */
    public function __construct(
        public string $reference,
        public string $text,
        public ?string $translation = null,
        public ?string $translationAbbr = null,
        public ?string $copyright = null,
        public array $verses = [],
        public ?string $provider = null,
    ) {}
}
