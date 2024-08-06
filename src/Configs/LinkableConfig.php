<?php

namespace Novius\LaravelLinkable\Configs;

use Closure;

class LinkableConfig
{
    /**
     * Example for $optionsQuery, $resolveQuery, $resolvePreviewQuery
     *
     * function(\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model $query) {
     *     $query->where('example', 'value');
     * }
     */
    public function __construct(
        public string $routeName,
        public string $routeParameterName,
        public string|Closure $optionLabel,
        public string $optionGroup,
        public ?Closure $optionsQuery = null,
        public ?Closure $resolveQuery = null,
        public ?Closure $resolveNotPreviewQuery = null,
        public ?string $previewTokenField = null
    ) {}
}
