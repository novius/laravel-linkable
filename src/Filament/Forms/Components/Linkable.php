<?php

namespace Novius\LaravelLinkable\Filament\Forms\Components;

use Filament\Forms\Components\ViewField;

class Linkable extends ViewField
{
    protected array $linkableClasses = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->view('laravel-linkable::linkable', [
            'linkableClasses' => $this->linkableClasses,
        ]);
    }

    public function linkableClasses(array $classes): static
    {
        $this->linkableClasses = $classes;

        return $this;
    }
}
