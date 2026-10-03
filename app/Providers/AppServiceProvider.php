<?php

namespace App\Providers;

use App\Services\Bible\ApiBibleProvider;
use App\Services\Bible\BibleApiComProvider;
use App\Services\Bible\BibleProvider;
use App\Services\Bible\ReferenceParser;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            ReferenceParser::class,
            static fn (): ReferenceParser => new ReferenceParser((array) config('bible.books', [])),
        );

        $this->app->singleton(
            ApiBibleProvider::class,
            static fn (): ApiBibleProvider => new ApiBibleProvider(
                config('bible.api_bible.key'),
                (string) config('bible.api_bible.base_url'),
            ),
        );

        $this->app->singleton(
            BibleApiComProvider::class,
            static fn (): BibleApiComProvider => new BibleApiComProvider(
                (string) config('bible.bible_api_com.base_url'),
            ),
        );

        $this->app->bind(BibleProvider::class, static function ($app): BibleProvider {
            return config('bible.provider', 'api_bible') === 'bible_api_com'
                ? $app->make(BibleApiComProvider::class)
                : $app->make(ApiBibleProvider::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
