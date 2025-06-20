<?php

namespace Novius\LaravelLinkable\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Novius\LaravelLinkable\Configs\LinkableConfig;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @mixin Model
 *
 * @phpstan-ignore trait.unused
 */
trait Linkable
{
    abstract public function linkableConfig(): ?LinkableConfig;

    public function url(): ?string
    {
        if ($this->linkableConfig() === null) {
            return null;
        }

        if (! $this->exists) {
            return null;
        }

        return $this->linkableConfig()->getUrl($this);
    }

    public function previewUrl(): ?string
    {
        if ($this->linkableConfig() === null) {
            return null;
        }

        if (! $this->exists) {
            return null;
        }

        $params = [];

        $previewTokenField = $this->linkableConfig()->previewTokenField;
        $guard = config('laravel-linkable.guard_preview');
        if (empty($guard) && $previewTokenField !== null) {
            $params['previewToken'] = $this->{$previewTokenField};
        }

        return $this->linkableConfig()->getUrl($this, $params);
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
}
