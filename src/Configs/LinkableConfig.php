<?php

namespace Novius\LaravelLinkable\Configs;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Novius\LaravelLinkable\Facades\Linkable;
use RuntimeException;

class LinkableConfig
{
    public array $optionSearch;

    /**
     * Example for $optionsQuery, $resolveQuery, $resolvePreviewQuery
     *
     * function(\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model $query) {
     *     $query->where('example', 'value');
     * }
     */
    public function __construct(
        public ?string $routeName,
        public ?string $routeParameterName,
        public string|Closure $optionLabel,
        public string $optionGroup,
        public ?Closure $optionsQuery = null,
        public ?Closure $resolveQuery = null,
        public ?Closure $resolveNotPreviewQuery = null,
        public ?string $previewTokenField = null,
        public ?Closure $getUrlCallback = null,
        ?array $optionSearch = null,
    ) {
        if ($getUrlCallback === null && $this->routeName === null) {
            throw new RuntimeException('You must set a route name or a closure to get the url');
        }
        if ($getUrlCallback === null && $this->routeName !== null && $this->routeParameterName === null) {
            throw new RuntimeException('You must set a route parameter name if you set a route name');
        }
        $this->optionSearch = $optionSearch ?? [];
        if ($optionSearch === null && is_string($optionLabel)) {
            $this->optionSearch = [$optionLabel];
        }
        if (class_exists('Filament\FilamentManager') && empty($this->optionSearch)) {
            throw new RuntimeException('You must set a search column or an array of search columns');
        }
    }

    public function getUrl(Model $model, array $extraParameters = []): ?string
    {
        if ($this->getUrlCallback !== null) {
            return call_user_func($this->getUrlCallback, $model, $extraParameters);
        }

        $locale = app()->getLocale();
        $localeColumn = Linkable::getModelLocaleColumn($model);
        if ($localeColumn !== null) {
            $locale = $model->{$localeColumn};
        }

        return Linkable::route($this->routeName, [
            ...$extraParameters,
            $this->routeParameterName => $model->{$model->getRouteKeyName()},
        ], $locale);
    }
}
