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

    public static function parseLyricsToSlides(?string $lyrics): array
    {
        if (!$lyrics || !trim($lyrics)) {
            return [];
        }
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $lyrics));
        $slides = [];
        $index = 0;
        for ($i = 0; $i < count($lines); $i += 2) {
            $content = trim($lines[$i]);
            if (isset($lines[$i + 1])) {
                $content .= "\n" . trim($lines[$i + 1]);
            }
            if (trim($content)) {
                $slides[] = ['slide_order' => $index++, 'content' => $content];
            }
        }
        return $slides;
    }
}
