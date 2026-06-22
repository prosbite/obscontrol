<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreScriptureRequest;
use App\Http\Requests\UpdateScriptureRequest;
use App\Http\Resources\ScriptureResource;
use App\Models\Scripture;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class ScriptureController
{
    public function index(): JsonResource
    {
        return ScriptureResource::collection(Scripture::orderBy('reference')->get());
    }

    public function store(StoreScriptureRequest $request): JsonResource
    {
        return new ScriptureResource(Scripture::create($request->validated()));
    }

    public function show(Scripture $scripture): JsonResource
    {
        return new ScriptureResource($scripture);
    }

    public function update(UpdateScriptureRequest $request, Scripture $scripture): JsonResource
    {
        $scripture->update($request->validated());
        return new ScriptureResource($scripture);
    }

    public function destroy(Scripture $scripture): JsonResponse
    {
        $scripture->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
