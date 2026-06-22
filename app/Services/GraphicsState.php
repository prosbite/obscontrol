<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class GraphicsState
{
    private const CACHE_KEY = 'graphics_state';

    public function get(): array
    {
        return Cache::get(self::CACHE_KEY, $this->default());
    }

    public function set(string $key, mixed $value): array
    {
        $state = $this->get();
        $state[$key] = $value;
        Cache::forever(self::CACHE_KEY, $state);
        return $state;
    }

    public function reset(): array
    {
        $default = $this->default();
        Cache::forever(self::CACHE_KEY, $default);
        return $default;
    }

    private function default(): array
    {
        return [
            'activeLowerThird' => null,
            'activeSong' => null,
            'activeSlide' => null,
            'lyricsVisible' => false,
            'lowerThirdVisible' => false,
            'activeScripture' => null,
            'scriptureVisible' => false,
            'timerRunning' => false,
            'timerPaused' => false,
            'timerDuration' => 600,
            'timerRemaining' => 600,
            'timerStartedAt' => null,
            'activeAnnouncement' => null,
            'announcementVisible' => false,
            'announcements' => [],
            'announcementIndex' => 0,
        ];
    }
}
