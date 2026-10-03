<?php

declare(strict_types=1);

namespace App\Services\Bible;

final readonly class BibleTranslation
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $abbreviation = null,
        public ?string $language = null,
        public ?string $provider = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'abbreviation' => $this->abbreviation,
            'language' => $this->language,
            'provider' => $this->provider,
        ];
    }
}
