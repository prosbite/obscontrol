<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Events\GraphicsEvent;
use App\Models\Announcement;
use App\Models\LowerThird;
use App\Models\Scripture;
use App\Models\Song;
use App\Services\GraphicsState;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ControlController
{
    public function __construct(
        private readonly GraphicsState $state,
    ) {}

    public function showLowerThird(Request $request): JsonResponse
    {
        $data = $request->validate(['id' => 'required|exists:lower_thirds,id']);
        $lowerThird = LowerThird::findOrFail($data['id']);
        $this->state->set('activeLowerThird', $lowerThird->toArray());
        $this->state->set('lowerThirdVisible', true);
        GraphicsEvent::dispatch('LowerThirdShown', ['lowerThird' => $lowerThird]);
        return response()->json($this->state->get());
    }

    public function hideLowerThird(): JsonResponse
    {
        $this->state->set('lowerThirdVisible', false);
        GraphicsEvent::dispatch('LowerThirdHidden');
        return response()->json($this->state->get());
    }

    public function showLyrics(Request $request): JsonResponse
    {
        $data = $request->validate(['song_id' => 'required|exists:songs,id']);
        $song = Song::with('slides')->findOrFail($data['song_id']);
        $this->state->set('activeSong', $song->toArray());
        $this->state->set('activeSlide', 0);
        $this->state->set('lyricsVisible', true);
        GraphicsEvent::dispatch('LyricsShown', [
            'song' => $song,
            'slide' => $song->slides->first()?->content,
        ]);
        return response()->json($this->state->get());
    }

    public function hideLyrics(): JsonResponse
    {
        $this->state->set('lyricsVisible', false);
        GraphicsEvent::dispatch('LyricsHidden');
        return response()->json($this->state->get());
    }

    public function nextSlide(): JsonResponse
    {
        $song = Song::with('slides')->find($this->state->get()['activeSong']['id'] ?? 0);
        if (!$song || !$song->slides->count()) {
            return response()->json($this->state->get());
        }
        $currentSlide = $this->state->get()['activeSlide'] ?? 0;
        $nextSlide = min($currentSlide + 1, $song->slides->count() - 1);
        $this->state->set('activeSlide', $nextSlide);
        GraphicsEvent::dispatch('LyricsSlideChanged', [
            'slide' => $song->slides->get($nextSlide)?->content,
            'slideIndex' => $nextSlide,
        ]);
        return response()->json($this->state->get());
    }

    public function previousSlide(): JsonResponse
    {
        $song = Song::with('slides')->find($this->state->get()['activeSong']['id'] ?? 0);
        if (!$song || !$song->slides->count()) {
            return response()->json($this->state->get());
        }
        $currentSlide = $this->state->get()['activeSlide'] ?? 0;
        $prevSlide = max(0, $currentSlide - 1);
        $this->state->set('activeSlide', $prevSlide);
        GraphicsEvent::dispatch('LyricsSlideChanged', [
            'slide' => $song->slides->get($prevSlide)?->content,
            'slideIndex' => $prevSlide,
        ]);
        return response()->json($this->state->get());
    }

    public function showScripture(Request $request): JsonResponse
    {
        $data = $request->validate(['id' => 'required|exists:scriptures,id']);
        $scripture = Scripture::findOrFail($data['id']);
        $this->state->set('activeScripture', $scripture->toArray());
        $this->state->set('scriptureVisible', true);
        GraphicsEvent::dispatch('ScriptureShown', ['scripture' => $scripture]);
        return response()->json($this->state->get());
    }

    public function hideScripture(): JsonResponse
    {
        $this->state->set('scriptureVisible', false);
        GraphicsEvent::dispatch('ScriptureHidden');
        return response()->json($this->state->get());
    }

    public function startTimer(Request $request): JsonResponse
    {
        $data = $request->validate(['duration' => 'required|integer|min:1|max:3600']);
        $this->state->set('timerDuration', $data['duration']);
        $this->state->set('timerRemaining', $data['duration']);
        $this->state->set('timerRunning', true);
        $this->state->set('timerPaused', false);
        $this->state->set('timerStartedAt', now()->timestamp);
        GraphicsEvent::dispatch('TimerStarted', ['duration' => $data['duration']]);
        return response()->json($this->state->get());
    }

    public function pauseTimer(): JsonResponse
    {
        $state = $this->state->get();
        $elapsed = now()->timestamp - ($state['timerStartedAt'] ?? now()->timestamp);
        $remaining = max(0, $state['timerDuration'] - $elapsed);
        $this->state->set('timerRemaining', $remaining);
        $this->state->set('timerRunning', false);
        $this->state->set('timerPaused', true);
        GraphicsEvent::dispatch('TimerPaused', ['remaining' => $remaining]);
        return response()->json($this->state->get());
    }

    public function resetTimer(): JsonResponse
    {
        $duration = $this->state->get()['timerDuration'];
        $this->state->set('timerRemaining', $duration);
        $this->state->set('timerRunning', false);
        $this->state->set('timerPaused', false);
        $this->state->set('timerStartedAt', null);
        GraphicsEvent::dispatch('TimerStopped');
        return response()->json($this->state->get());
    }

    public function stopTimer(): JsonResponse
    {
        $this->state->set('timerRunning', false);
        $this->state->set('timerPaused', false);
        $this->state->set('timerStartedAt', null);
        GraphicsEvent::dispatch('TimerStopped');
        return response()->json($this->state->get());
    }

    public function showAnnouncement(Request $request): JsonResponse
    {
        $data = $request->validate(['id' => 'required|exists:announcements,id']);
        $announcement = Announcement::findOrFail($data['id']);
        $announcements = Announcement::orderBy('id')->get();
        $index = $announcements->search(fn ($a) => $a->id === (int) $data['id']);
        $this->state->set('activeAnnouncement', $announcement->toArray());
        $this->state->set('announcementVisible', true);
        $this->state->set('announcements', $announcements->toArray());
        $this->state->set('announcementIndex', $index !== false ? $index : 0);
        GraphicsEvent::dispatch('AnnouncementShown', ['announcement' => $announcement]);
        return response()->json($this->state->get());
    }

    public function hideAnnouncement(): JsonResponse
    {
        $this->state->set('announcementVisible', false);
        GraphicsEvent::dispatch('AnnouncementHidden');
        return response()->json($this->state->get());
    }

    public function nextAnnouncement(): JsonResponse
    {
        $announcements = Announcement::orderBy('id')->get();
        if (!$announcements->count()) {
            return response()->json($this->state->get());
        }
        $index = min($this->state->get()['announcementIndex'] + 1, $announcements->count() - 1);
        $announcement = $announcements->get($index);
        $this->state->set('activeAnnouncement', $announcement?->toArray());
        $this->state->set('announcementIndex', $index);
        $this->state->set('announcements', $announcements->toArray());
        GraphicsEvent::dispatch('AnnouncementShown', ['announcement' => $announcement]);
        return response()->json($this->state->get());
    }

    public function previousAnnouncement(): JsonResponse
    {
        $announcements = Announcement::orderBy('id')->get();
        if (!$announcements->count()) {
            return response()->json($this->state->get());
        }
        $index = max(0, $this->state->get()['announcementIndex'] - 1);
        $announcement = $announcements->get($index);
        $this->state->set('activeAnnouncement', $announcement?->toArray());
        $this->state->set('announcementIndex', $index);
        $this->state->set('announcements', $announcements->toArray());
        GraphicsEvent::dispatch('AnnouncementShown', ['announcement' => $announcement]);
        return response()->json($this->state->get());
    }
}
