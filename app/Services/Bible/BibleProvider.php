<?php

declare(strict_types=1);

namespace App\Services\Bible;

interface BibleProvider
{
    /**
     * Machine name, e.g. "api_bible".
     */
    public function name(): string;

    /**
     * Whether the provider has everything it needs to make live calls.
     */
    public function configured(): bool;

    /**
     * @return BibleTranslation[]
     */
    public function translations(): array;

    /**
     * Whether the provider can serve the given translation identifier.
     */
    public function supports(string $translation): bool;

    /**
     * @throws BibleProviderException
     */
    public function passage(string $translation, PassageReference $reference): BiblePassage;
}
