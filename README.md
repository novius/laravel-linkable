# Laravel Linkable

[![Novius CI](https://github.com/novius/laravel-linkable/actions/workflows/main.yml/badge.svg?branch=main)](https://github.com/novius/laravel-linkable/actions/workflows/main.yml)
[![Packagist Release](https://img.shields.io/packagist/v/novius/laravel-linkable.svg?maxAge=1800&style=flat-square)](https://packagist.org/packages/novius/laravel-linkable)
[![License: AGPL v3](https://img.shields.io/badge/License-AGPL%20v3-blue.svg)](http://www.gnu.org/licenses/agpl-3.0)


## Introduction

A package to manage Laravel Eloquent models linkable.

Provide a Linkable Nova Field.

## Requirements

* PHP >= 8.2
* Laravel 10.0

## Installation

You can install the package via composer:

```bash
composer require novius/laravel-linkable
```

Optionally, you can also: 

```bash
php artisan vendor:publish --provider="Novius\LaravelLinkable\LaravelLinkableServiceProvider" --tag=config
php artisan vendor:publish --provider="Novius\LaravelLinkable\LaravelLinkableServiceProvider" --tag=lang
```

## Usage

### Eloquent Model Trait

```php
namespace App\Models;

use \Illuminate\Database\Eloquent\Model;
use Novius\LaravelLinkable\Traits\Linkable;

class Post extends Model {
    use Linkable;
    ...

    public function getLinkableConfig(): LinkableModelConfig
    {
        if (! isset($this->linkableConfig)) {
            $this->linkableConfig = new LinkableModelConfig(
                // To retrieve a instance url, you can define the getUrlCallback
                getUrlCallback: function (Model $model, array $extraParameters = []) {
                    return route('post_route', [
                        ...$extraParameters,
                        'post' => $model,
                    ], true, $model->locale)  
                },

                // Or your can juste define the route name and the route name parameter
                routeName: 'post_route',
                routeParameterName: 'post',

                optionLabel: 'title', // Required. A field of your model or a closure (taking the model instance as parameter) returning a label. Use to display a model instance in the Linkable Nova field
                optionGroup: 'My model', // Required. The name of the group of the model in the Linkable Nova field
                optionsQuery: function (Builder|Page $query) { // Optional. To modify the default query to populate the Linkable Nova field  
                    $query->published();
                },
                resolveQuery: function (Builder|Page $query) { // Optional. The base query to resolve the model binding
                    $query->where('locale', app()->currentLocale());
                },
                resolveNotPreviewQuery: function (Builder|Page $query) { // Optional. The query to resolve the model binding when not in preview mode
                    $query->published();
                },
                previewTokenField: 'preview_token' // Optional. The field that contains the preview token of the model 
            );
        }

        return $this->linkableConfig;
    }
```

Now you can do that:

```php

// In your route file, if you choose to work with `routeName` and `routeParameterName`
Route::get('post/{post}', function (Post $post) {
    // ...
})->name('post_route');

$post = Post::first();
$post->url();
$post->previewUrl();
```

### Nova

If you use Laravel Nova, you can now use the Linkable field :

```php
<?php

use Novius\LaravelLinkable\Nova\Fields\Linkable;

class MyResource extends Resource
{
    public static $model = \App\Models\MyResource::class;

    public function fields(NovaRequest $request)
    {
        return [
            // ...

            Linkable::make('Link', 'link')
                ->linkableClasses([  // Optional: if you want to restrict link types 
                    'route',
                    OtherModel::class,                     
                ]),
        ];
    }
}

```

### Filament

If you use Laravel Filament, you can now use the Linkable field :

```php
<?php

use Novius\LaravelLinkable\Filament\Forms\Components\Linkable;

class MyResource extends Resource
{
    public static $model = \App\Models\MyResource::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // ...
    
                Linkable::make('link')
                    ->label('Link')
                    ->linkableClasses([  // Optional: if you want to restrict link types 
                        'route',
                        OtherModel::class,                     
                    ]),
            ]);
    }
}

```

### Retrieving link

Now you can do that:

```php
use Novius\LaravelLinkable\Facades\Linkable;

$model = MyResource::first();
echo Linkable::getLink($model->link);
```

#### Configuration

```php
return [
    // Laravel Linkable will autoload all Model using Linkable trait in this directory
    'autoload_models_in' => app_path('Models'),

    // The guard name to preview a model without using the preview token
    'guard_preview' => null,

    /*
     * Sometimes you need to link items that are not objects.
     *
     * This config allows you to link routes.
     *     For instance: 'contact' => 'page.contact'
     *
     * "contact" will be the parameter of the laravel function route().
     * "page.contact" will be the parameter of the laravel function trans().
     */
    'linkable_routes' => [
        'my_route' => 'My route',    
    ],

    /*
     * If you want to add specific models that use the linkable trait and that do not appear in your `autoload_models_in` directory
     */
    'linkable_models' => [
        \Provider\Package\Models\Model::class,
    ],

    // Set to `true` if you don't want to use localization
    'disable_localization' => false,
];
```

If you want to customize the way Linkable generates URLs (by default the native `route()` method of Laravel), you can define your own method.
Indispensable if you use a localization package for the routes.
Add this in your ServiceProvider:

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Novius\LaravelLinkable\Facades\Linkable;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->booted(function () {
            Linkable::setRouteCallback(static function (string $name, array $parameters = [], ?string $locale = null) {
                // This is an example of a package adding a `locale` to the route method
                return route($name, $parameters, true, $locale);
            });
        });
    }
}
```

### Testing

```bash
composer run test
```

## CS Fixer

Lint your code with Laravel Pint using:

```bash
composer run cs-fix
```

## Licence

This package is under [GNU Affero General Public License v3](http://www.gnu.org/licenses/agpl-3.0.html) or (at your option) any later version.
