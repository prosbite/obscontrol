<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreLowerThirdRequest;
use App\Http\Requests\UpdateLowerThirdRequest;
use App\Http\Resources\LowerThirdResource;
use App\Models\LowerThird;
use Illuminate\Http\Resources\Json\JsonResource;

class LowerThirdController
{
    public function index(): JsonResource
    {
        return LowerThirdResource::collection(LowerThird::orderBy('name')->get());
    }

    public function store(StoreLowerThirdRequest $request): JsonResource
    {
        $lowerThird = LowerThird::create($request->validated());
        return new LowerThirdResource($lowerThird);
    }

    public function show(LowerThird $lowerThird): JsonResource
    {
        return new LowerThirdResource($lowerThird);
    }

    public function update(UpdateLowerThirdRequest $request, LowerThird $lowerThird): JsonResource
    {
        $lowerThird->update($request->validated());
        return new LowerThirdResource($lowerThird);
    }

    public function destroy(LowerThird $lowerThird): \Illuminate\Http\JsonResponse
    {
        $lowerThird->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
