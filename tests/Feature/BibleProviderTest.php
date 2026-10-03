<?php

use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'bible.provider' => 'api_bible',
        'bible.api_bible.key' => 'test-key',
        'bible.api_bible.base_url' => 'https://api.scripture.api.bible/v1',
    ]);
});

it('maps an API.Bible passage response', function () {
    Http::fake([
        'api.scripture.api.bible/v1/bibles/*/passages/*' => Http::response([
            'data' => [
                'id' => 'JHN.3.16-JHN.3.18',
                'reference' => 'John 3:16-18',
                'content' => '  For God so loved the world...  ',
                'copyright' => 'Scripture quotations taken from the King James Version.',
                'abbreviation' => 'KJV',
            ],
        ], 200),
    ]);

    $passage = makeBibleService()->passage('John 3:16-18', 'bible-1');

    expect($passage->text)->toBe('For God so loved the world...')
        ->and($passage->reference)->toBe('John 3:16-18')
        ->and($passage->translationAbbr)->toBe('KJV')
        ->and($passage->copyright)->toContain('King James')
        ->and($passage->provider)->toBe('api_bible');

    Http::assertSent(fn ($request) => $request->hasHeader('api-key', 'test-key')
        && str_contains($request->url(), '/passages/JHN.3.16-JHN.3.18'));
});

it('falls back to bible-api.com when API.Bible fails', function () {
    Http::fake([
        'api.scripture.api.bible/*' => Http::response([], 500),
        'bible-api.com/*' => Http::response([
            'reference' => 'John 3:16',
            'text' => 'For God so loved the world.',
            'translation_id' => 'web',
            'translation_name' => 'World English Bible',
            'translation_note' => 'Public Domain',
        ], 200),
    ]);

    $passage = makeBibleService()->passage('John 3:16', 'web');

    expect($passage->provider)->toBe('bible_api_com')
        ->and($passage->text)->toBe('For God so loved the world.')
        ->and($passage->translationAbbr)->toBe('WEB');
});
