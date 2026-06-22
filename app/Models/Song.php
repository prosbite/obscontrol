<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Song extends Model
{
    protected $fillable = [
        'title',
        'artist',
        'category',
        'lyrics',
    ];

    public function slides(): HasMany
    {
        return $this->hasMany(SongSlide::class)->orderBy('slide_order');
    }

    private static function getSectionHeader(string $line): ?string
    {
        if (preg_match('/^(verse|refrain|chorus|coda|bridge|intro|outro|pre-chorus|interlude|ending|tag|solo)\s*\d*$/i', trim($line))) {
            return trim($line);
        }
        return null;
    }

    public static function parseLyricsToSlides(?string $lyrics): array
    {
        if (!$lyrics || !trim($lyrics)) {
            return [];
        }
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $lyrics));
        $buffer = [];
        $currentSection = null;
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }
            $header = self::getSectionHeader($trimmed);
            if ($header !== null) {
                $currentSection = $header;
                continue;
            }
            $buffer[] = ['text' => $trimmed, 'section' => $currentSection];
        }
        $slides = [];
        $index = 0;
        for ($i = 0; $i < count($buffer); $i += 2) {
            $content = $buffer[$i]['text'];
            $section = $buffer[$i]['section'];
            if (isset($buffer[$i + 1])) {
                $content .= "\n" . $buffer[$i + 1]['text'];
            }
            $slide = ['slide_order' => $index++, 'content' => $content];
            if ($section !== null) {
                $slide['section_label'] = $section;
            }
            $slides[] = $slide;
        }
        return $slides;
    }
}