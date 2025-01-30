<?php

namespace Novius\LaravelLinkable\Nova\Fields;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Fields\FormData;
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

        $optionCallback = function ($locale) {
            return LinkableFacade::links($this->optionsClasses, $locale)
                ->sortBy('label')
                ->sortBy('group')
                ->mapWithKeys(fn ($item) => [
                    $item['type'].':'.$item['id'] => [
                        'label' => $item['label'], 'group' => $item['group'],
                    ],
                ]);
        };

        if (config('laravel-linkable.use_localization', false)) {
            $novaRequest = app(NovaRequest::class);
            $model = $novaRequest->findResource()->model();
            if ($model && in_array('Novius\LaravelTranslatable\Traits\Translatable', class_uses_recursive($model), true)) {
                /** @var Model&Translatable $model */
                $this->dependsOn(
                    [$model->getLocaleColumn()],
                    function (Linkable $field, NovaRequest $request, FormData $formData) use ($optionCallback, $model) {
                        $field->options($optionCallback($formData->string($model->getLocaleColumn())));
                    }
                );
            }
        }

        $this->searchable();
        $this->displayUsingLabels();
        $this->options(function () use ($optionCallback) {
            $locale = null;
            if (config('laravel-linkable.use_localization', false)) {
                $novaRequest = app(NovaRequest::class);
                $model = $novaRequest->findResource()->resource;
                if ($model && in_array('Novius\LaravelTranslatable\Traits\Translatable', class_uses_recursive($model), true)) {
                    /** @var Model&Translatable $model */
                    $locale = $model->{$model->getLocaleColumn()};
                }
            }

            return $optionCallback($locale);
        });
    }

    public function optionsClasses(array $classes): static
    {
        $this->optionsClasses = $classes;

        return $this;
    }
}
