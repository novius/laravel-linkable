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

Optionally you can also: 

```bash
php artisan vendor:publish --provider="Novius\LaravelLinkable\LaravelLinkableServiceProvider" --tag=config
php artisan vendor:publish --provider="Novius\LaravelLinkable\LaravelLinkableServiceProvider" --tag=lang
```

## Usage

#### Eloquent Model Trait

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

// In your routes file
Route::get('post/{post}', function (Post $post) {
    // ...
})->name('post_route');

$post = Post::first();
$post->url();
$post->previewUrl();
```

#### Nova

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

            Linkable::make('Link', 'link'),
        ];
    }
}

```

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

    // The guard name to preview model, without using the preview token
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
     * If you want to add specifics models using the Linkable trait that are not in your autoload directory
     */

    'linkable_models' => [
        Provider\Package\Models\Model::class,
    ],

    /*
     * Enable this if your site use multiple locales and Laravel Localization package
     */
    'use_localization' => false,
];
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
