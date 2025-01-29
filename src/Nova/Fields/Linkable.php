<?php

namespace Novius\LaravelLinkable\Nova\Fields;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;
use Novius\LaravelLinkable\Facades\Linkable as LinkableFacade;
use Novius\LaravelTranslatable\Traits\Translatable;

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
            $locale = null;
            if (class_exists('Laravel\Nova\Http\Requests\NovaRequest') && config('laravel-linkable.use_localization', false)) {
                $novaRequest = app(NovaRequest::class);
                $model = $novaRequest->findResourceOrFail()->resource;
                if (in_array('Novius\LaravelTranslatable\Traits\Translatable', class_uses_recursive($model), true)) {
                    /** @var Model&Translatable $model */
                    $locale = $model->{$model->getLocaleColumn()};
                }
            }

            return LinkableFacade::links($this->optionsClasses, $locale)
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
