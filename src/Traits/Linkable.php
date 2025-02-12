<?php

namespace Novius\LaravelLinkable\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Novius\LaravelLinkable\Configs\LinkableConfig;
use Novius\LaravelTranslatable\Traits\Translatable;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/** @mixin Model */
trait Linkable
{
    abstract public function linkableConfig(): ?LinkableConfig;

    public function url(): ?string
    {
        if ($this->linkableConfig() === null) {
            return null;
        }

        $routeName = $this->linkableConfig()->routeName;
        $parameter = $this->linkableConfig()->routeParameterName;

        if ($routeName === null || ! $this->exists || $parameter === null) {
            return null;
        }

        $locale = app()->getLocale();
        if (config('laravel-linkable.use_localization', false) &&
            in_array('Novius\LaravelTranslatable\Traits\Translatable', class_uses_recursive(__CLASS__), true)
        ) {
            /** @var Model&Translatable $this */
            $locale = $this->{$this->getLocaleColumn()};
        }

        return route($routeName, [$parameter => $this->{$this->getRouteKeyName()}], true, $locale);
    }

    public function previewUrl(): ?string
    {
        if ($this->linkableConfig() === null) {
            return null;
        }

        $routeName = $this->linkableConfig()->routeName;
        $parameter = $this->linkableConfig()->routeParameterName;

        if ($routeName === null || ! $this->exists || $parameter === null) {
            return null;
        }

        $params = [$parameter => $this->{$this->getRouteKeyName()}];

        $previewTokenField = $this->linkableConfig()->previewTokenField;
        $guard = config('laravel-linkable.guard_preview');
        if (empty($guard) && $previewTokenField !== null) {
            $params['previewToken'] = $this->{$previewTokenField};
        }

        $locale = app()->getLocale();
        if (config('laravel-linkable.use_localization', false) &&
            in_array('Novius\LaravelTranslatable\Traits\Translatable', class_uses_recursive(__CLASS__), true)
        ) {
            /** @var Model&Translatable $this */
            $locale = $this->{$this->getLocaleColumn()};
        }

        return route($routeName, $params, true, $locale);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        /** @var Model $this */
        if ($this->linkableConfig() === null) {
            return $this->resolveRouteBindingQuery($this, $value, $field)->first();
        }

        $guard = config('laravel-linkable.guard_preview');
        /** @var Model|Builder $query */
        $query = $this->newQuery();
        if ($this->linkableConfig()->resolveQuery) {
            call_user_func($this->linkableConfig()->resolveQuery, $query);
        }

        if (! empty($guard) && Auth::guard($guard)->check()) {
            return $this->resolveRouteBindingQuery($query, $value, $field)->first();
        }

        if (request()?->has('previewToken')) {
            $query->where('preview_token', request()?->get('previewToken'));

            return $this->resolveRouteBindingQuery($query, $value, $field)->first();
        }

        $queryNotPreview = $query->clone();
        if ($this->linkableConfig()->resolveNotPreviewQuery) {
            call_user_func($this->linkableConfig()->resolveNotPreviewQuery, $queryNotPreview);
        }

        return $this->resolveRouteBindingQuery($queryNotPreview, $value, $field)->first();
    }

    public static function linkableItems(?string $locale = null): Collection
    {
        $config = (new static)->linkableConfig();
        if ($config === null) {
            return collect();
        }

        $query = $config->optionsQuery ? call_user_func($config->optionsQuery, static::query()) : static::query();
        if ($locale && config('laravel-linkable.use_localization', false) &&
            in_array(Translatable::class, class_uses_recursive(__CLASS__), true)) {
            $query->withLocale($locale);
        }

        return $query->get()->map(function (Model $item) use ($config) {
            $label = is_callable($config->optionLabel) ? call_user_func($config->optionLabel, $item) : $item->{$config->optionLabel};

            return [
                'id' => $item->{$item->getKeyName()},
                'type' => get_class($item),
                'group' => $config->optionGroup,
                'label' => $label,
            ];
        });
    }
}
