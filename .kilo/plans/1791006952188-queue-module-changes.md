# Queue Module Enhancements

## Goal

Three changes to the queue module, plus a cleanup for deleted sources:

1. Show each queue item's real **title + subtitle** in the card (currently only a stored `name` snapshot is shown).
2. The queue card's **Edit** button opens the *same editor as the underlying source* (Song or LowerThird); edits update the source and are automatically reflected in the queue.
3. **Drag-and-drop** reordering of queue items.

## Locked Decisions

- **Live resolution**: a queue item keeps only `type` + `source_id`; title/subtitle are resolved live from the source `Song`/`LowerThird`. The queue-stored `name` is no longer used for display (no queue-only rename/title).
- **Field mapping**: LowerThird -> title = `name`, subtitle = `subtitle`; Song -> title = `title`, subtitle = `artist`.
- **Drag & drop**: add `vuedraggable@4.1.0` (SortableJS-based) and a new bulk reorder endpoint; **remove the ▲/▼ arrow buttons**.
- **Orphans**: queue items whose source no longer exists are **auto-removed when the queue is loaded**.

## Current State (reference)

- `app/Models/Queue.php` — items live in a JSON `items` column. Canonical item: `{id (uuid), name, type ('lowerthird'|'lyrics'), source_id, position, created_at}`. Methods: `addItem`, `updateItem`, `removeItem`, `moveItem`.
- `app/Http/Controllers/Api/QueueController.php` — `index/store/show/update/destroy`, `addItem/updateItem/removeItem/moveItem`.
- `app/Http/Resources/QueueResource.php` — maps items to output; has fallback reads for a legacy nested `$item['queue']['type'|'item_id'|'song_id']` shape.
- `routes/api.php:25-29` — queue routes.
- `resources/js/Pages/Control/Dashboard.vue` — all queue UI/state (`queues`, `selectedQueueId`, `queueItems`, `currentQueueIndex`, `expandedQueueItemId`), plus source edit modals `openEditLt`/`saveLt` and `openEditSong`/`saveSong` and arrays `lowerThirds`/`songs`.
- `resources/js/types/graphics.ts:77-92` — `QueueItemResource`, `QueueSet`.
- Tests: Pest (`composer test` / `php artisan test`); formatting: `vendor/bin/pint`. No existing queue tests.

## Backend Tasks

1. **`app/Models/Queue.php`**
   - Add a normalizer/resolver for an item that handles both the canonical shape and the legacy nested `queue` shape, producing `{id, type, source_id, position, created_at}` (keep `name` if present).
   - Add `pruneOrphans(): array` — normalize all items, drop any whose `type`/`source_id` does not resolve to an existing `LowerThird` (type `lowerthird`) or `Song` (type `lyrics`), reindex `position` sequentially, persist only if something changed, and return the resulting canonical items.
   - Add `reorderItems(array $orderedIds): bool` — require an exact permutation of the current item ids (same count and every id present); on success sort items by the given order, reindex `position`, save, return `true`; otherwise return `false` without change.
2. **`app/Http/Controllers/Api/QueueController.php`**
   - `show`: call `$queue->pruneOrphans()` before building the `QueueResource` response.
   - Add `reorder(Request $request, Queue $queue)`: validate `item_ids` as `required|array` of strings; call `reorderItems`; return `QueueResource` on success, `422 {message}` when the id set does not match.
   - `moveItem`: call `pruneOrphans()` before returning (keeps the legacy endpoint consistent; UI will stop using it).
3. **`routes/api.php`**
   - Add `Route::patch('queues/{queue}/items/reorder', [QueueController::class, 'reorder']);` alongside the existing item routes. No conflict with existing `PUT/DELETE items/{item}` or `PATCH items/{item}/move`.
4. **`app/Http/Resources/QueueResource.php`**
   - No required change (frontend resolves title/subtitle from its already-loaded source lists). Keep existing legacy fallbacks so old rows still serialize. Optionally simplify once pruning normalizes rows — not required.

## Frontend Tasks (`resources/js/Pages/Control/Dashboard.vue`)

1. **Dependency**: `npm install vuedraggable@4.1.0` (pulls `sortablejs`), then `import draggable from 'vuedraggable'`.
2. **Helpers**:
   - `queueSource(item)` -> find in `lowerThirds` (by `source_id`) for `lowerthird`, else in `songs` for `lyrics`; return `null` if missing.
   - `queueTitle(item)` -> source title, else `item.name`, else `'Unavailable'`.
   - `queueSubtitle(item)` -> source subtitle/artist, else `''`.
3. **Card display**: keep the type label (`Lower Third` / `Song`) and Live badge. Replace the `item.name` heading with `queueTitle(item)`, and render `queueSubtitle(item)` as a muted line when non-empty. Update the "Now playing" bar to use `queueTitle(...)`.
4. **Edit**: add `editQueueItem(item)` -> look up source and call `openEditLt(source)` / `openEditSong(source)`; if the source is missing, toast an error. Remove `renameQueueItem` (function + button) and the queue-only rename. Source save handlers already replace the object in `lowerThirds`/`songs`, so the queue display updates automatically.
5. **Drag & drop**: wrap the item cards in `<draggable v-model="queueItems" item-key="id" tag="div" class="space-y-3" handle=".queue-drag-handle" :animation="150" @end="onQueueReorder">` with the card rendered via the `#item="{ element: item, index: idx }"` slot. Add a drag handle element with class `queue-drag-handle` on each card.
   - `onQueueReorder()`: `PATCH /api/queues/{selectedQueueId}/items/reorder` with `{ item_ids: queueItems.map(i => i.id) }`; on success replace `queueItems` with the response items; on failure re-fetch the queue. Remove `moveQueueItem` and the ▲/▼ buttons.
6. **Playback tracking under reorder**: replace `currentQueueIndex` state with an id-based `currentQueueItemId` (set in `showQueueItem`). Derive the current index via `computed` for the Live badge, disable states, and Prev/Next. This keeps the playing item highlighted correctly after drag reordering. If the playing id disappears (pruned/deleted), reset the player state.
7. **Types** (`resources/js/types/graphics.ts`): `QueueItemResource.name` becomes optional/deprecated if it is no longer guaranteed; no other shape changes.

## Edge Cases / Failure Modes

- Reordering while an item is playing: id-based tracking keeps the Live highlight and Prev/Next correct.
- Duplicate additions of the same source: allowed; unique item ids keep drag/reorder stable.
- Source deleted while its queue card is open: pruned on next queue load; if it was playing, reset player.
- Legacy nested-shape rows: normalized during `pruneOrphans()` before serialization.
- Reorder payload not matching the current id set: reject `422`, UI re-fetches to resync.
- Empty/single-item queue: drag target is a no-op; existing empty-state UI unchanged.

## Validation

- Backend: add `tests/Feature/QueueTest.php` (Pest) covering: add item; `reorder` with a valid permutation updates positions; `reorder` with a mismatched id set returns 422; `show` removes an orphaned item and reindexes positions. Run `php artisan test --filter=Queue`.
- Format PHP: `vendor/bin/pint --dirty`.
- Frontend typecheck: `npx vue-tsc --noEmit`.
- Build: `npm run build`.
- Manual: add a LowerThird and a Song to a queue; confirm title/subtitle render from the source; edit each from the queue and confirm the source list and queue both update; drag to reorder and confirm persistence after reload; delete a source and reload the queue to confirm the card is removed.

## Notes / Risks

- `npm install` is required for `vuedraggable`; restart `npm run dev`/Vite after install.
- The stored `name` field is retained for backward compatibility and existing rows but is no longer displayed.
- Keep `moveItem` backend endpoint for compatibility even though the UI no longer calls it; it can be removed in a later cleanup.

## Out of Scope

- Moving items between queues, cross-type queue behavior, and auto-advance playback.
