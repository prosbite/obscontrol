<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreSongRequest;
use App\Http\Requests\UpdateSongRequest;
use App\Http\Resources\SongResource;
use App\Models\Song;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class SongController
{
    public function index(): JsonResource
    {
        return SongResource::collection(Song::with('slides')->orderBy('title')->get());
    }

    public function store(StoreSongRequest $request): JsonResource
    {
        $song = Song::create($request->validated());
        if ($slides = $request->validated()['slides'] ?? []) {
            $song->slides()->createMany($slides);
        }
        return new SongResource($song->load('slides'));
    }

    public function show(Song $song): JsonResource
    {
        return new SongResource($song->load('slides'));
    }

    public function update(UpdateSongRequest $request, Song $song): JsonResource
    {
        $song->update($request->validated());
        if ($request->has('slides')) {
            $song->slides()->delete();
            $song->slides()->createMany($request->validated()['slides']);
        }
        return new SongResource($song->load('slides'));
    }

    public function destroy(Song $song): JsonResponse
    {
        $song->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
