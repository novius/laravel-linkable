<?php

namespace Novius\LaravelLinkable\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\ViewField;

class Linkable extends ViewField
{
    protected array|Closure|null $linkableClasses = null;

    protected Closure|string|null $locale = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->view('laravel-linkable::linkable');
    }

    public function setLinkableClasses(array|Closure $classes): static
    {
        $this->linkableClasses = $classes;

        return $this;
    }

    public function getLinkableClasses(): array
    {
        return (array) ($this->evaluate($this->linkableClasses) ?? []);
    }

    public function setLocale(string|Closure $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->evaluate($this->locale);
    }
}
