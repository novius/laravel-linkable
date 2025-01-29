<?php

namespace Novius\LaravelLinkable\Service;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Novius\LaravelLinkable\Traits\Linkable;
use ReflectionException;
use Symfony\Component\Finder\Finder;

class LinkableService
{
    protected Collection $models;

    protected Collection $routes;

    /**
     * @throws ReflectionException
     */
    public function __construct(array $config)
    {
        $this->models = collect(Arr::get($config, 'linkable_models', []));
        $this->routes = collect(Arr::get($config, 'linkable_routes', []));
        $this->autoloadTemplates(Arr::get($config, 'autoload_models_in'));
    }

    public function addModels(array $classes): void
    {
        $models = $this->models->merge($classes)->unique();
        $this->models = $models->filter(function ($model) use ($models) {
            return ! $models->filter(function ($parent) use ($model) {
                return in_array($model, class_parents($parent), true);
            })->count();
        });
    }

    public function addRoutes(array $routes): void
    {
        $this->routes = $this->routes->merge($routes);
    }

    public function links(array $classes = [], ?string $locale = null): Collection
    {
        $links = collect();
        foreach ($this->models as $class) {
            if ((empty($classes) || in_array($class, $classes, true)) &&
                in_array(Linkable::class, class_uses_recursive($class), true)
            ) {
                /** @var Linkable $class */
                $links = $links->merge($class::linkableItems($locale));
            }
        }

        if (empty($classes) || in_array('route', $classes, true)) {
            foreach ($this->routes as $routeName => $translation) {
                if (Route::has($routeName)) {
                    $links->push([
                        'id' => $routeName,
                        'type' => 'route',
                        'group' => trans('laravel-linkable::linkable.route_group'),
                        'label' => trans($translation),
                    ]);
                }
            }
        }

        return $links;
    }

    public function getLink(string $key): ?string
    {
        $infos = explode(':', $key);
        if ($infos[0] === 'route') {
            if (Route::has($infos[1])) {
                return route($infos[1]);
            }
        } elseif (count($infos) === 2) {
            /** @var class-string<Model> $className */
            $className = $infos[0];

            if (in_array(Linkable::class, class_uses_recursive($className), true)) {
                /** @var Model&Linkable $item */
                $item = $className::find($infos[1]);

                return $item?->url();
            }
        }

        return null;
    }

    /**
     * @throws ReflectionException
     */
    protected function autoloadTemplates(?string $dir = null): void
    {
        $namespace = app()->getNamespace();

        if ($dir === null) {
            return;
        }

        $models = [];
        foreach ((new Finder)->in($dir)->files() as $model) {
            $model = $namespace.str_replace(
                ['/', '.php'],
                ['\\', ''],
                Str::after($model->getPathname(), app_path().DIRECTORY_SEPARATOR)
            );

            if (in_array(Linkable::class, class_uses_recursive($model), true) &&
                ! (new \ReflectionClass($model))->isAbstract()
            ) {
                $models[] = $model;
            }
        }

        $this->addModels($models);
    }
}
