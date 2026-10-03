<?php

use App\Models\LowerThird;
use App\Models\Queue;
use App\Models\Song;
use App\Models\User;

test('an item can be added to a queue', function () {
    $user = User::factory()->create();
    $lowerThird = LowerThird::create(['name' => 'Guest Speaker', 'template' => 'classic']);
    $queue = Queue::create(['name' => 'Main']);

    $response = $this->actingAs($user)->postJson("/api/queues/{$queue->id}/items", [
        'name' => 'Guest Speaker',
        'type' => 'lowerthird',
        'source_id' => $lowerThird->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.type', 'lowerthird')
        ->assertJsonPath('data.source_id', $lowerThird->id)
        ->assertJsonPath('data.position', 0);

    expect($queue->fresh()->items)->toHaveCount(1);
});

test('reorder with a valid permutation updates positions', function () {
    $user = User::factory()->create();
    $lowerThird = LowerThird::create(['name' => 'Speaker', 'template' => 'classic']);
    $song = Song::create(['title' => 'Amazing Grace']);
    $queue = Queue::create(['name' => 'Main']);

    $first = $queue->addItem(['name' => 'Speaker', 'type' => 'lowerthird', 'source_id' => $lowerThird->id]);
    $second = $queue->addItem(['name' => 'Amazing Grace', 'type' => 'lyrics', 'source_id' => $song->id]);

    $response = $this->actingAs($user)->patchJson("/api/queues/{$queue->id}/items/reorder", [
        'item_ids' => [$second['id'], $first['id']],
    ]);

    $response->assertOk()
        ->assertJsonPath('data.items.0.id', $second['id'])
        ->assertJsonPath('data.items.0.position', 0)
        ->assertJsonPath('data.items.1.id', $first['id'])
        ->assertJsonPath('data.items.1.position', 1);

    $items = $queue->fresh()->items;
    expect($items[0]['id'])->toBe($second['id']);
    expect($items[0]['position'])->toBe(0);
    expect($items[1]['id'])->toBe($first['id']);
    expect($items[1]['position'])->toBe(1);
});

test('reorder with a mismatched id set returns 422 without changing the queue', function () {
    $user = User::factory()->create();
    $lowerThird = LowerThird::create(['name' => 'Speaker', 'template' => 'classic']);
    $queue = Queue::create(['name' => 'Main']);

    $first = $queue->addItem(['name' => 'Speaker', 'type' => 'lowerthird', 'source_id' => $lowerThird->id]);
    $second = $queue->addItem(['name' => 'Speaker', 'type' => 'lowerthird', 'source_id' => $lowerThird->id]);

    $response = $this->actingAs($user)->patchJson("/api/queues/{$queue->id}/items/reorder", [
        'item_ids' => [$first['id'], 'not-a-real-item-id'],
    ]);

    $response->assertStatus(422);

    $items = $queue->fresh()->items;
    expect($items)->toHaveCount(2);
    expect($items[0]['id'])->toBe($first['id']);
    expect($items[1]['id'])->toBe($second['id']);
});

test('show removes orphaned items and reindexes positions', function () {
    $user = User::factory()->create();
    $lowerThird = LowerThird::create(['name' => 'Speaker', 'template' => 'classic']);
    $queue = Queue::create(['name' => 'Main']);

    $first = $queue->addItem(['name' => 'Speaker', 'type' => 'lowerthird', 'source_id' => $lowerThird->id]);
    $queue->addItem(['name' => 'Missing Song', 'type' => 'lyrics', 'source_id' => 999999]);
    $last = $queue->addItem(['name' => 'Speaker', 'type' => 'lowerthird', 'source_id' => $lowerThird->id]);

    $response = $this->actingAs($user)->getJson("/api/queues/{$queue->id}");

    $response->assertOk();
    $items = $response->json('data.items');

    expect($items)->toHaveCount(2);
    expect($items[0]['id'])->toBe($first['id']);
    expect($items[0]['position'])->toBe(0);
    expect($items[1]['id'])->toBe($last['id']);
    expect($items[1]['position'])->toBe(1);

    expect($queue->fresh()->items)->toHaveCount(2);
});

test('show normalizes legacy nested queue items', function () {
    $user = User::factory()->create();
    $lowerThird = LowerThird::create(['name' => 'Speaker', 'template' => 'classic']);
    $queue = Queue::create(['name' => 'Legacy']);

    $queue->items = [[
        'id' => 'legacy-item',
        'name' => 'Speaker',
        'position' => 0,
        'created_at' => now()->toDateTimeString(),
        'queue' => ['type' => 'lowerthird', 'item_id' => $lowerThird->id],
    ]];
    $queue->save();

    $response = $this->actingAs($user)->getJson("/api/queues/{$queue->id}");

    $response->assertOk()
        ->assertJsonPath('data.items.0.id', 'legacy-item')
        ->assertJsonPath('data.items.0.type', 'lowerthird')
        ->assertJsonPath('data.items.0.source_id', $lowerThird->id);

    expect($queue->fresh()->items[0])->not->toHaveKey('queue');
});
