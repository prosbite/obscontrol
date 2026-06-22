<?php

use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\ControlController;
use App\Http\Controllers\Api\LowerThirdController;
use App\Http\Controllers\Api\ScriptureController;
use App\Http\Controllers\Api\SongController;
use App\Services\GraphicsState;
use Illuminate\Support\Facades\Route;

Route::get('/control/state', function (GraphicsState $state) {
    return response()->json($state->get());
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::apiResource('lower-thirds', LowerThirdController::class);
    Route::apiResource('songs', SongController::class);
    Route::apiResource('scriptures', ScriptureController::class);
    Route::apiResource('announcements', AnnouncementController::class);

    Route::prefix('control')->group(function () {
        Route::post('lowerthird/show', [ControlController::class, 'showLowerThird']);
        Route::post('lowerthird/hide', [ControlController::class, 'hideLowerThird']);
        Route::post('lyrics/show', [ControlController::class, 'showLyrics']);
        Route::post('lyrics/hide', [ControlController::class, 'hideLyrics']);
        Route::post('lyrics/next', [ControlController::class, 'nextSlide']);
        Route::post('lyrics/previous', [ControlController::class, 'previousSlide']);
        Route::post('scripture/show', [ControlController::class, 'showScripture']);
        Route::post('scripture/hide', [ControlController::class, 'hideScripture']);
        Route::post('timer/start', [ControlController::class, 'startTimer']);
        Route::post('timer/pause', [ControlController::class, 'pauseTimer']);
        Route::post('timer/reset', [ControlController::class, 'resetTimer']);
        Route::post('timer/stop', [ControlController::class, 'stopTimer']);
        Route::post('announcement/show', [ControlController::class, 'showAnnouncement']);
        Route::post('announcement/hide', [ControlController::class, 'hideAnnouncement']);
        Route::post('announcement/next', [ControlController::class, 'nextAnnouncement']);
        Route::post('announcement/previous', [ControlController::class, 'previousAnnouncement']);
    });
});
