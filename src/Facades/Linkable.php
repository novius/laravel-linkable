<?php

namespace Novius\LaravelLinkable\Facades;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Novius\LaravelLinkable\Service\LinkableService;

/**
 * @method static void addModels(array $classes)
 * @method static void addRoutes(array $routes)
 * @method static Collection groups(array $classes = [])
 * @method static Collection links(array $classes = [], ?string $locale = null)
 * @method static string|null getLink(string $key)
 * @method static void setRouteCallback(Closure $callback)
 * @method static string|null route(string $name, array $parameters = [], ?string $locale = null)
 * @method static string|null getModelLocaleColumn(Model|string $model):
 *
 * @mixin  LinkableService
 */
class Linkable extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LinkableService::class;
    }
}
