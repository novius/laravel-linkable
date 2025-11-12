<?php

namespace Novius\LaravelLinkable\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Novius\LaravelLinkable\Traits\Linkable;

class LinkableChanged
{
    use Dispatchable, SerializesModels;

    /**
     * @param  Model&Linkable  $linkable
     *
     * @phpstan-ignore parameter.unresolvableType,property.unresolvableType
     */
    public function __construct(public Model $linkable) {}
}
