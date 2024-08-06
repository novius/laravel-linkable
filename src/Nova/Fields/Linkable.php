<?php

namespace Novius\LaravelLinkable\Nova\Fields;

use Laravel\Nova\Fields\Select;
use Novius\LaravelLinkable\Facades\Linkable as LinkableFacade;

class Linkable extends Select
{
    public array $optionsClasses = [];

    public function __construct(
        $name,
        $attribute = null,
        ?callable $resolveCallback = null
    ) {
        parent::__construct($name, $attribute, $resolveCallback);

        $this->searchable();
        $this->displayUsingLabels();
        $this->options(function () {
            return LinkableFacade::links($this->optionsClasses)
                ->sortBy('label')
                ->sortBy('group')
                ->mapWithKeys(fn ($item) => [
                    $item['type'].':'.$item['id'] => [
                        'label' => $item['label'], 'group' => $item['group'],
                    ],
                ]);
        });
    }

    public function optionsClasses(array $classes): static
    {
        $this->optionsClasses = $classes;

        return $this;
    }
}
