<?php

namespace Novius\LaravelLinkable\Facades;

use Illuminate\Support\Facades\Facade;
use Novius\LaravelLinkable\Service\LinkableService;

/**
 * @mixin  LinkableService
 */
class Linkable extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LinkableService::class;
    }
}
