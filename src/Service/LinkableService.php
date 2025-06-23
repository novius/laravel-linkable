<?php

namespace Novius\LaravelLinkable\Service;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Novius\LaravelLinkable\Facades\Linkable as LinkableFacade;
use Novius\LaravelLinkable\Traits\Linkable;
use ReflectionException;
use Symfony\Component\Finder\Finder;

class LinkableService
{
    protected Collection $models;

    protected Collection $routes;

    protected ?Closure $routesCallback = null;

    /**
     * @throws ReflectionException
     */
    public function __construct(array $config)
    {
        $this->models = collect(Arr::get($config, 'linkable_models', []));
        $this->routes = collect(Arr::get($config, 'linkable_routes', []));
        $this->autoloadModels(Arr::get($config, 'autoload_models_in'));
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
                /** @var Model&Linkable $model */
                $model = new $class;
                $config = $model->linkableConfig();
                if ($config === null) {
                    continue;
                }

                $query = $config->optionsQuery ? call_user_func($config->optionsQuery, $model::query()) : $model::query();
                if ($locale !== null && LinkableFacade::getModelLocaleColumn(__CLASS__) !== null) {
                    $query->withLocale($locale);
                }

                $links = $links->merge(
                    $query->get()->map(function (Model $item) use ($config) {
                        $label = is_callable($config->optionLabel) ? call_user_func($config->optionLabel, $item) : $item->{$config->optionLabel};

                        return [
                            'id' => $item->{$item->getKeyName()},
                            'type' => get_class($item),
                            'group' => $config->optionGroup,
                            'label' => $label,
                        ];
                    })
                );
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

    public function groups(array $classes = []): Collection
    {
        $groups = collect();
        foreach ($this->models as $class) {
            if ((empty($classes) || in_array($class, $classes, true)) &&
                in_array(Linkable::class, class_uses_recursive($class), true)
            ) {
                /** @var Model&Linkable $model */
                $model = new $class;
                $config = $model->linkableConfig();
                if ($config !== null) {
                    $groups->put($class, $config->optionGroup);
                }
            }
        }

        if (empty($classes) || in_array('route', $classes, true)) {
            foreach ($this->routes as $routeName => $translation) {
                if (Route::has($routeName)) {
                    $groups->push('route', trans('laravel-linkable::linkable.route_group'));
                }
            }
        }

        return $groups;
    }

    public function getLink(string $key): ?string
    {
        $infos = explode(':', $key);
        if ($infos[0] === 'route') {
            if (Route::has($infos[1])) {
                return $this->route($infos[1]);
            }
        } elseif (count($infos) === 2) {
            /** @var class-string<Model> $className */
            $className = $infos[0];

            if (in_array(Linkable::class, class_uses_recursive($className), true)) {
                /** @var Model&Linkable|null $item */
                $item = $className::find($infos[1]);

                /** @phpstan-ignore method.notFound */
                return $item?->url();
            }
        }

        return null;
    }

    /**
     * @param  Closure(string, array, ?string): ?string  $callback
     */
    public function setRouteCallback(Closure $callback): void
    {
        $this->routesCallback = $callback;
    }

    public function route(string $name, array $parameters = [], ?string $locale = null): ?string
    {
        if ($this->routesCallback === null) {
            /** @phpstan-ignore arguments.count */
            return route($name, $parameters, true, $locale);
        }

        return call_user_func($this->routesCallback, $name, $parameters, $locale);
    }

    public function getModelLocaleColumn(Model|string $model): ?string
    {
        if (is_string($model) && class_exists($model) && in_array(Model::class, class_parents($model), true)) {
            $model = new $model;
        }
        if (in_array('Novius\LaravelTranslatable\Traits\Translatable', class_uses_recursive($model), true)) {
            if (method_exists($model, 'translatableConfig')) {
                return $model->translatableConfig()->locale_column;
            }
            if (method_exists($model, 'getLocaleColumn')) {
                return $model->getLocaleColumn();
            }
        }

        return null;
    }

    /**
     * @throws ReflectionException
     */
    protected function autoloadModels(?string $dir = null): void
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
