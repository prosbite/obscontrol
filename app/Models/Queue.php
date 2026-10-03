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

    public function pruneOrphans(): array
    {
        $items = $this->items ?? [];
        $resolved = [];

        foreach ($items as $item) {
            $canonical = $this->normalizeItem($item);
            if (! $this->sourceExists($canonical['type'], $canonical['source_id'])) {
                continue;
            }
            $canonical['position'] = count($resolved);
            $resolved[] = $canonical;
        }

        if ($resolved !== $items) {
            $this->items = $resolved;
            $this->save();
        }

        return $resolved;
    }

    public function reorderItems(array $orderedIds): bool
    {
        $items = $this->items ?? [];

        $currentIds = array_map(fn (array $item) => (string) ($item['id'] ?? ''), $items);
        $requestedIds = array_map('strval', array_values($orderedIds));

        if (! $this->isExactPermutation($currentIds, $requestedIds)) {
            return false;
        }

        $byId = [];
        foreach ($items as $item) {
            $byId[(string) ($item['id'] ?? '')] = $item;
        }

        $reordered = [];
        foreach ($requestedIds as $id) {
            $item = $byId[$id];
            $item['position'] = count($reordered);
            $reordered[] = $item;
        }

        if ($reordered !== $items) {
            $this->items = $reordered;
            $this->save();
        }

        return true;
    }

    private function normalizeItem(array $item): array
    {
        $legacy = is_array($item['queue'] ?? null) ? $item['queue'] : [];
        $type = $item['type'] ?? $legacy['type'] ?? null;
        $sourceId = $item['source_id'] ?? $legacy['item_id'] ?? $legacy['song_id'] ?? null;

        $normalized = ['id' => $item['id'] ?? (string) Str::uuid()];
        if (array_key_exists('name', $item)) {
            $normalized['name'] = $item['name'];
        }
        $normalized['type'] = $type;
        $normalized['source_id'] = $sourceId !== null ? (int) $sourceId : null;
        $normalized['position'] = (int) ($item['position'] ?? 0);
        $normalized['created_at'] = $item['created_at'] ?? $this->created_at?->toDateTimeString();

        return $normalized;
    }

    private function sourceExists(?string $type, ?int $sourceId): bool
    {
        if ($type === null || $sourceId === null) {
            return false;
        }

        return match ($type) {
            'lowerthird' => LowerThird::whereKey($sourceId)->exists(),
            'lyrics' => Song::whereKey($sourceId)->exists(),
            default => false,
        };
    }

    private function isExactPermutation(array $current, array $requested): bool
    {
        if (count($current) !== count($requested)) {
            return false;
        }

        $sortedCurrent = $current;
        $sortedRequested = $requested;
        sort($sortedCurrent);
        sort($sortedRequested);

        return $sortedCurrent === $sortedRequested;
    }
}
