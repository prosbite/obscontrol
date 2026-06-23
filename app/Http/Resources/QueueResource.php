<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QueueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'items' => array_map(fn (array $item) => [
                'id' => $item['id'],
                'name' => $item['name'],
                'type' => $item['type'] ?? ($item['queue']['type'] ?? null),
                'source_id' => $item['source_id'] ?? ($item['queue']['item_id'] ?? $item['queue']['song_id'] ?? null),
                'position' => $item['position'],
                'created_at' => $item['created_at'] ?? $this->created_at,
            ], $this->items ?? []),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
