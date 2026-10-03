<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Active Provider
    |--------------------------------------------------------------------------
    |
    | "api_bible" uses the API.Bible v1 REST API (needs API_BIBLE_KEY).
    | "bible_api_com" uses the zero-config public-domain bible-api.com service.
    |
    */
    'provider' => env('BIBLE_PROVIDER', 'api_bible'),

    /*
    |--------------------------------------------------------------------------
    | Default Translation
    |--------------------------------------------------------------------------
    |
    | Used when a request omits a translation and the fallback provider is
    | active. For API.Bible the default is the configured bible id below.
    |
    */
    'default_translation' => env('BIBLE_API_DEFAULT_TRANSLATION', 'kjv'),

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Freshness window for cached passages. API.Bible Starter requires content
    | to be refreshed at least every 30 days, so the value is clamped to 30.
    |
    */
    'cache_ttl_days' => (int) env('BIBLE_CACHE_TTL_DAYS', 7),

    'api_bible' => [
        'key' => env('API_BIBLE_KEY'),
        'base_url' => env('API_BIBLE_BASE_URL', 'https://api.scripture.api.bible/v1'),
        'default_bible_id' => env('API_BIBLE_DEFAULT_BIBLE_ID', 'de4e12af7f28d72c-01'),
        'translations_cache_minutes' => 1440,
    ],

    'bible_api_com' => [
        'base_url' => env('BIBLE_API_COM_BASE_URL', 'https://bible-api.com'),
    ],

    'books' => require __DIR__.'/bible_books.php',
];
