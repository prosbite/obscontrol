<?php

declare(strict_types=1);

namespace App\Services\Bible;

/**
 * A single continuous Bible reference range.
 */
final readonly class PassageReference
{
    public function __construct(
        public string $book,
        public ?int $chapter,
        public ?int $verseStart,
        public ?int $verseEnd,
        public ?int $chapterEnd,
        public ?int $verseEndEnd,
        public string $label,
    ) {}

    /**
     * Canonical USFM passage id segment (e.g. JHN.3.16-JHN.3.18).
     */
    public function canonical(): string
    {
        $start = $this->book;
        if ($this->chapter !== null) {
            $start .= '.'.$this->chapter;
        }
        if ($this->verseStart !== null) {
            $start .= '.'.$this->verseStart;
        }

        if ($this->chapterEnd !== null) {
            $end = $this->book.'.'.$this->chapterEnd;
            if ($this->verseEndEnd !== null) {
                $end .= '.'.$this->verseEndEnd;
            }

            return $start.'-'.$end;
        }

        if ($this->verseEnd !== null && $this->verseEnd !== $this->verseStart) {
            $end = $this->book;
            if ($this->chapter !== null) {
                $end .= '.'.$this->chapter;
            }
            $end .= '.'.$this->verseEnd;

            return $start.'-'.$end;
        }

        return $start;
    }
}
