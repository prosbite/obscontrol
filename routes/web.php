<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('control.dashboard')
        : redirect()->route('login');
});

Route::get('/display/main', function () {
    return Inertia::render('Display/Main');
})->name('display.main');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/control', function () {
        return Inertia::render('Control/Dashboard');
    })->name('control.dashboard');
});

require __DIR__.'/auth.php';
