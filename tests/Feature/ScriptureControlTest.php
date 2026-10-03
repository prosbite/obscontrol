<?php

use App\Events\GraphicsEvent;
use App\Models\User;
use App\Services\GraphicsState;
use Illuminate\Support\Facades\Event;

it('requires authentication to show a scripture reference', function () {
    $this->postJson('/api/control/scripture/show-reference', [
        'reference' => 'John 3:16',
        'text' => 'For God so loved the world.',
    ])->assertUnauthorized();
});

it('shows a reference-based scripture and broadcasts it', function () {
    Event::fake([GraphicsEvent::class]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/control/scripture/show-reference', [
            'reference' => 'John 3:16',
            'text' => 'For God so loved the world.',
            'translation' => 'KJV',
            'translation_abbr' => 'KJV',
            'provider' => 'bible_api_com',
        ])
        ->assertOk()
        ->assertJsonPath('scriptureVisible', true)
        ->assertJsonPath('activeScripture.reference', 'John 3:16');

    $state = app(GraphicsState::class)->get();

    expect($state['scriptureVisible'])->toBeTrue()
        ->and($state['activeScripture']['text'])->toBe('For God so loved the world.')
        ->and($state['activeScripture']['translation_abbr'])->toBe('KJV');

    Event::assertDispatched(
        GraphicsEvent::class,
        fn (GraphicsEvent $event) => $event->event === 'ScriptureShown'
            && $event->data['scripture']['reference'] === 'John 3:16',
    );
});

it('validates the reference payload', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/control/scripture/show-reference', ['reference' => ''])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['reference', 'text']);
});
