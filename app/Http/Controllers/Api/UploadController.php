<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class UploadController
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp,svg', 'max:2048'],
        ]);

        $path = $request->file('image')->store('lowerthirds', 'public');

        return response()->json(['path' => Storage::disk('public')->url($path)]);
    }
}
