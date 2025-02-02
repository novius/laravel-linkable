<?php

namespace Novius\LaravelLinkable;

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Novius\LaravelLinkable\Service\LinkableService;
use Novius\LaravelLinkable\Tests\Http\Controllers\LinkableController;

class LaravelLinkableServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([__DIR__.'/../config/laravel-linkable.php' => config_path('laravel-linkable.php')], 'config');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'laravel-linkable');
        $this->publishes([__DIR__.'/../lang' => lang_path('vendor/laravel-linkable')], 'lang');

        if ($this->app->runningUnitTests()) {
            Route::middleware(SubstituteBindings::class)->get('/model/{model}', [LinkableController::class, 'show']);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel-linkable.php', 'laravel-linkable'
        );

        $this->app->singleton(LinkableService::class, function () {
            return new LinkableService(config('laravel-linkable'));
        });
    }
}
