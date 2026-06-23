<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Queue extends Model
{
    protected $fillable = [
        'name',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
        ];
    }

    public function addItem(array $data): array
    {
        $items = $this->items ?? [];
        $item = [
            'id' => (string) Str::uuid(),
            'name' => $data['name'],
            'type' => $data['type'],
            'source_id' => (int) $data['source_id'],
            'position' => count($items),
            'created_at' => now()->toDateTimeString(),
        ];
        $items[] = $item;
        $this->items = $items;
        $this->save();
        return $item;
    }

    public function updateItem(string $id, array $data): ?array
    {
        $items = $this->items ?? [];
        foreach ($items as &$item) {
            if ($item['id'] === $id) {
                $item = array_merge($item, $data);
                $this->items = $items;
                $this->save();
                return $item;
            }
        }
        return null;
    }

    public function removeItem(string $id): bool
    {
        $items = $this->items ?? [];
        $filtered = array_values(array_filter($items, fn ($i) => ($i['id'] ?? null) !== $id));
        if (count($filtered) === count($items)) {
            return false;
        }
        $filtered = array_map(fn ($i, $idx) => array_merge($i, ['position' => $idx]), $filtered, array_keys($filtered));
        $this->items = $filtered;
        $this->save();
        return true;
    }

    public function moveItem(string $id, string $direction): bool
    {
        $items = $this->items ?? [];
        $currentIdx = null;
        foreach ($items as $i => $item) {
            if (($item['id'] ?? null) === $id) {
                $currentIdx = $i;
                break;
            }
        }
        if ($currentIdx === null) {
            return false;
        }

        $targetIdx = $direction === 'up' ? $currentIdx - 1 : $currentIdx + 1;
        if ($targetIdx < 0 || $targetIdx >= count($items)) {
            return false;
        }

        [$items[$currentIdx], $items[$targetIdx]] = [$items[$targetIdx], $items[$currentIdx]];
        $items = array_values($items);
        $items = array_map(fn ($i, $idx) => array_merge($i, ['position' => $idx]), $items, array_keys($items));
        $this->items = $items;
        $this->save();
        return true;
    }
}
