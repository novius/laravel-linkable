<?php

namespace Novius\LaravelLinkable\Tests\Http\Controllers;

use Novius\LaravelLinkable\Tests\Models\LinkableModel;

class LinkableController
{
    public function show(LinkableModel $model): string
    {
        return $model->title;
    }
}
