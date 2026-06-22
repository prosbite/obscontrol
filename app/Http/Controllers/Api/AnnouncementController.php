<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Requests\UpdateAnnouncementRequest;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementController
{
    public function index(): JsonResource
    {
        return AnnouncementResource::collection(Announcement::orderBy('title')->get());
    }

    public function store(StoreAnnouncementRequest $request): JsonResource
    {
        return new AnnouncementResource(Announcement::create($request->validated()));
    }

    public function show(Announcement $announcement): JsonResource
    {
        return new AnnouncementResource($announcement);
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement): JsonResource
    {
        $announcement->update($request->validated());
        return new AnnouncementResource($announcement);
    }

    public function destroy(Announcement $announcement): JsonResponse
    {
        $announcement->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
