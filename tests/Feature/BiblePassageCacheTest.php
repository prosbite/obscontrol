<?php

use App\Models\BiblePassage as BiblePassageModel;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'bible.provider' => 'api_bible',
        'bible.api_bible.key' => 'test-key',
        'bible.api_bible.base_url' => 'https://api.scripture.api.bible/v1',
        'bible.cache_ttl_days' => 7,
    ]);
});

it('serves a repeated lookup from cache without another http call', function () {
    Http::fake([
        'api.scripture.api.bible/*' => Http::response([
            'data' => ['reference' => 'John 3:16', 'content' => 'Cached text', 'abbreviation' => 'KJV'],
        ], 200),
    ]);

    $service = makeBibleService();
    $first = $service->passage('John 3:16', 'bible-1');
    $second = $service->passage('John 3:16', 'bible-1');

    expect($first->text)->toBe('Cached text')
        ->and($second->text)->toBe('Cached text');

    Http::assertSentCount(1);
    expect(BiblePassageModel::count())->toBe(1);
});

it('refreshes a stale cached passage', function () {
    BiblePassageModel::create([
        'provider' => 'api_bible',
        'translation' => 'bible-1',
        'reference_key' => 'api_bible:bible-1:JHN.3.16',
        'label' => 'John 3:16',
        'text' => 'Old text',
        'translation_abbr' => 'KJV',
        'fetched_at' => now()->subDays(10),
    ]);

    Http::fake([
        'api.scripture.api.bible/*' => Http::response([
            'data' => ['reference' => 'John 3:16', 'content' => 'Fresh text', 'abbreviation' => 'KJV'],
        ], 200),
    ]);

    $passage = makeBibleService()->passage('John 3:16', 'bible-1');

    expect($passage->text)->toBe('Fresh text');
    Http::assertSentCount(1);
});

it('returns a stale cached passage when the providers fail', function () {
    BiblePassageModel::create([
        'provider' => 'api_bible',
        'translation' => 'bible-1',
        'reference_key' => 'api_bible:bible-1:JHN.3.16',
        'label' => 'John 3:16',
        'text' => 'Stale text',
        'fetched_at' => now()->subDays(10),
    ]);

    Http::fake([
        'api.scripture.api.bible/*' => Http::response([], 500),
    ]);

    $passage = makeBibleService()->passage('John 3:16', 'bible-1');

    expect($passage->text)->toBe('Stale text');
});
