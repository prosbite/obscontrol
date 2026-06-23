<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\QueueResource;
use App\Models\Queue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QueueController
{
    public function index(): JsonResponse
    {
        $queues = Queue::orderBy('id')->get();

        return response()->json(['data' => $queues->map(fn (Queue $q) => [
            'id' => $q->id,
            'name' => $q->name,
            'created_at' => $q->created_at,
            'updated_at' => $q->updated_at,
        ])]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $queue = Queue::create(['name' => $data['name']]);

        return response()->json([
            'data' => [
                'id' => $queue->id,
                'name' => $queue->name,
                'created_at' => $queue->created_at,
                'updated_at' => $queue->updated_at,
            ],
        ], 201);
    }

    public function show(Queue $queue): JsonResponse
    {
        return response()->json(['data' => (new QueueResource($queue))->toArray(request())]);
    }

    public function update(Request $request, Queue $queue): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $queue->update(['name' => $data['name']]);

        return response()->json([
            'data' => [
                'id' => $queue->id,
                'name' => $queue->name,
                'created_at' => $queue->created_at,
                'updated_at' => $queue->updated_at,
            ],
        ]);
    }

    public function destroy(Queue $queue): JsonResponse
    {
        $queue->delete();

        return response()->json(null, 204);
    }

    public function addItem(Request $request, Queue $queue): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:lowerthird,lyrics',
            'source_id' => 'required|integer',
        ]);

        $item = $queue->addItem($data);

        return response()->json(['data' => $item], 201);
    }

    public function updateItem(Request $request, Queue $queue, string $item): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
        ]);

        $updated = $queue->updateItem($item, $data);

        if (!$updated) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        return response()->json(['data' => $updated]);
    }

    public function removeItem(Queue $queue, string $item): JsonResponse
    {
        $removed = $queue->removeItem($item);

        if (!$removed) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        return response()->json(null, 204);
    }

    public function moveItem(Request $request, Queue $queue, string $item): JsonResponse
    {
        $data = $request->validate([
            'direction' => 'required|in:up,down',
        ]);

        $moved = $queue->moveItem($item, $data['direction']);

        if (!$moved) {
            return response()->json(['message' => 'Cannot move item'], 422);
        }

        return response()->json(['data' => (new QueueResource($queue))->toArray(request())]);
    }
}
