<?php

namespace Novius\LaravelLinkable\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Novius\LaravelLinkable\Configs\LinkableConfig;
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

        return route($routeName, [$parameter => $this->{$this->getRouteKeyName()}]);
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

        return route($routeName, $params);
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

        $guard = config('laravel-nova-page-manager.guard_preview');
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

    public static function linkableItems(): Collection
    {
        $config = (new static)->linkableConfig();
        if ($config === null) {
            return collect();
        }

        $query = $config->optionsQuery ? call_user_func($config->optionsQuery, static::query()) : static::query();

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
