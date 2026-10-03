<?php

declare(strict_types=1);

namespace App\Services\Bible;

/**
 * Parses free-form human references (e.g. "John 3:16-18; Rom 5:8") into a
 * flat list of canonical {@see PassageReference} ranges.
 */
class ReferenceParser
{
    private const SINGLE_CHAPTER_BOOKS = ['OBA', 'PHM', '2JN', '3JN', 'JUD'];

    /** @var array<string, string> normalized alias => USFM book id */
    private array $aliases = [];

    /** @var array<string, string> USFM book id => display name */
    private array $names = [];

    /**
     * @param  array<string, array{name: string, aliases?: array<int, string>}>  $books
     */
    public function __construct(array $books)
    {
        foreach ($books as $id => $book) {
            $this->names[$id] = $book['name'];

            foreach (array_merge([$book['name']], $book['aliases'] ?? []) as $alias) {
                $key = $this->normalize($alias);
                if ($key !== '' && ! isset($this->aliases[$key])) {
                    $this->aliases[$key] = $id;
                }
            }
        }

        uksort($this->aliases, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));
    }

    /**
     * @return PassageReference[]
     *
     * @throws ReferenceParseException
     */
    public function parse(string $input): array
    {
        $input = trim($input);
        if ($input === '') {
            throw new ReferenceParseException('A scripture reference is required.');
        }

        $references = [];

        foreach (preg_split('/;/', $input) ?: [] as $chunk) {
            $chunk = trim($chunk);
            if ($chunk === '') {
                continue;
            }

            $currentBook = null;
            $currentChapter = null;
            $intervals = [];
            $locked = false;

            foreach (explode(',', $chunk) as $rawToken) {
                $token = trim($rawToken);
                if ($token === '') {
                    continue;
                }

                if (preg_match('/^\d+(\s*-\s*\d+)?$/', $token) === 1) {
                    if ($currentBook === null || $currentChapter === null || $locked) {
                        throw new ReferenceParseException("Invalid verse continuation \"{$token}\".");
                    }

                    $intervals[] = $this->parseVerseRange($token);

                    continue;
                }

                $this->flush($references, $currentBook, $currentChapter, $intervals);
                $intervals = [];
                $locked = false;

                $spec = $this->parseBookToken($token);
                $currentBook = $spec['book'];
                $currentChapter = $spec['chapter'];

                if ($spec['special']) {
                    $references[] = new PassageReference(
                        book: $spec['book'],
                        chapter: $spec['chapter'],
                        verseStart: $spec['verseStart'],
                        verseEnd: $spec['verseEnd'],
                        chapterEnd: $spec['chapterEnd'],
                        verseEndEnd: $spec['verseEndEnd'],
                        label: $spec['label'],
                    );
                    $locked = true;

                    continue;
                }

                $intervals[] = [$spec['verseStart'], $spec['verseEnd']];
            }

            $this->flush($references, $currentBook, $currentChapter, $intervals);
        }

        if ($references === []) {
            throw new ReferenceParseException('No valid scripture reference was found.');
        }

        return $references;
    }

    /**
     * @param  PassageReference[]  $references
     * @param  array<int, array{0: int, 1: int}>  $intervals
     */
    private function flush(array &$references, ?string $book, ?int $chapter, array $intervals): void
    {
        if ($intervals === [] || $book === null || $chapter === null) {
            return;
        }

        usort($intervals, static fn (array $a, array $b): int => $a[0] <=> $b[0]);

        $merged = [];
        foreach ($intervals as [$start, $end]) {
            $last = count($merged) - 1;
            if ($last >= 0 && $start <= $merged[$last][1] + 1) {
                $merged[$last][1] = max($merged[$last][1], $end);

                continue;
            }
            $merged[] = [$start, $end];
        }

        $name = $this->names[$book] ?? $book;
        foreach ($merged as [$start, $end]) {
            $label = $start === $end
                ? "{$name} {$chapter}:{$start}"
                : "{$name} {$chapter}:{$start}-{$end}";

            $references[] = new PassageReference($book, $chapter, $start, $end, null, null, $label);
        }
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function parseVerseRange(string $token): array
    {
        if (preg_match('/^(\d+)\s*-\s*(\d+)$/', $token, $matches) === 1) {
            return [(int) $matches[1], (int) $matches[2]];
        }

        $verse = (int) $token;

        return [$verse, $verse];
    }

    /**
     * @return array{book: string, chapter: int, verseStart: ?int, verseEnd: ?int, chapterEnd: ?int, verseEndEnd: ?int, special: bool, label: string}
     */
    private function parseBookToken(string $token): array
    {
        $compact = $this->normalize($token);

        $book = null;
        $matchedLength = 0;

        foreach ($this->aliases as $alias => $id) {
            $length = strlen($alias);
            if (! str_starts_with($compact, $alias)) {
                continue;
            }

            $next = $compact[$length] ?? null;
            if ($next !== null && ! ctype_digit($next)) {
                continue;
            }

            $book = $id;
            $matchedLength = $length;

            break;
        }

        if ($book === null) {
            throw new ReferenceParseException("Could not recognize the book in \"{$token}\".");
        }

        $name = $this->names[$book];
        $remainder = substr($compact, $matchedLength);
        $singleChapter = in_array($book, self::SINGLE_CHAPTER_BOOKS, true);

        if ($remainder === '') {
            return $this->spec($book, 1, null, null, null, null, true, "{$name} 1");
        }

        if (preg_match('/^(\d+)$/', $remainder, $matches) === 1) {
            $number = (int) $matches[1];

            if ($singleChapter) {
                return $this->spec($book, 1, $number, $number, null, null, false, "{$name} 1:{$number}");
            }

            return $this->spec($book, $number, null, null, null, null, true, "{$name} {$number}");
        }

        if (preg_match('/^(\d+):(\d+)-(\d+):(\d+)$/', $remainder, $matches) === 1) {
            $chapter = (int) $matches[1];
            $verseStart = (int) $matches[2];
            $chapterEnd = (int) $matches[3];
            $verseEndEnd = (int) $matches[4];

            return $this->spec(
                $book,
                $chapter,
                $verseStart,
                null,
                $chapterEnd,
                $verseEndEnd,
                true,
                "{$name} {$chapter}:{$verseStart}-{$chapterEnd}:{$verseEndEnd}",
            );
        }

        if (preg_match('/^(\d+):(\d+)-(\d+)$/', $remainder, $matches) === 1) {
            $chapter = (int) $matches[1];
            $verseStart = (int) $matches[2];
            $verseEnd = (int) $matches[3];

            return $this->spec($book, $chapter, $verseStart, $verseEnd, null, null, false, "{$name} {$chapter}:{$verseStart}-{$verseEnd}");
        }

        if (preg_match('/^(\d+):(\d+)$/', $remainder, $matches) === 1) {
            $chapter = (int) $matches[1];
            $verse = (int) $matches[2];

            return $this->spec($book, $chapter, $verse, $verse, null, null, false, "{$name} {$chapter}:{$verse}");
        }

        throw new ReferenceParseException("Could not parse \"{$token}\".");
    }

    /**
     * @return array{book: string, chapter: int, verseStart: ?int, verseEnd: ?int, chapterEnd: ?int, verseEndEnd: ?int, special: bool, label: string}
     */
    private function spec(
        string $book,
        int $chapter,
        ?int $verseStart,
        ?int $verseEnd,
        ?int $chapterEnd,
        ?int $verseEndEnd,
        bool $special,
        string $label,
    ): array {
        return [
            'book' => $book,
            'chapter' => $chapter,
            'verseStart' => $verseStart,
            'verseEnd' => $verseEnd,
            'chapterEnd' => $chapterEnd,
            'verseEndEnd' => $verseEndEnd,
            'special' => $special,
            'label' => $label,
        ];
    }

    private function normalize(string $value): string
    {
        $value = strtolower($value);
        $value = str_replace(['.', '’', "'"], '', $value);

        return preg_replace('/\s+/', '', $value) ?? '';
    }
}
