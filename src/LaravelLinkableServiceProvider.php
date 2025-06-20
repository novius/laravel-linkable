<?php

namespace Novius\LaravelLinkable;

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Novius\LaravelLinkable\Livewire\LinkableFields;
use Novius\LaravelLinkable\Service\LinkableService;
use Novius\LaravelLinkable\Tests\Http\Controllers\LinkableController;

class LaravelLinkableServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-linkable');

        $this->publishes([__DIR__.'/../config/laravel-linkable.php' => config_path('laravel-linkable.php')], 'config');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'laravel-linkable');
        $this->publishes([__DIR__.'/../lang' => lang_path('vendor/laravel-linkable')], 'lang');

        if ($this->app->runningUnitTests() && is_dir(__DIR__.'/../tests')) {
            Route::middleware(SubstituteBindings::class)->get('/model/{model}', [LinkableController::class, 'show']);
        }

        if (class_exists('Livewire\Livewire')) {
            Livewire::component('laravel-linkable::linkable-fields', LinkableFields::class);
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
