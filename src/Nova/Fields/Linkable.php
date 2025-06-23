<?php

namespace Novius\LaravelLinkable\Nova\Fields;

use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;
use LaravelLang\Locales\Facades\Locales;
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

        if (! config('laravel-linkable.disable_localization') && Locales::installed()->count() >= 2) {
            $novaRequest = app(NovaRequest::class);
            $model = $novaRequest->findResource()->model();
            $localeColumn = LinkableFacade::getModelLocaleColumn($model);
            if ($localeColumn !== null) {
                $this->dependsOn(
                    [$localeColumn],
                    function (Linkable $field, NovaRequest $request, FormData $formData) use ($localeColumn, $optionCallback) {
                        $field->options($optionCallback($formData->string($localeColumn)));
                    }
                );
            }
        }

        $this->searchable();
        $this->displayUsingLabels();
        $this->options(function () use ($optionCallback) {
            $locale = null;
            if (! config('laravel-linkable.disable_localization') && Locales::installed()->count() >= 2) {
                $novaRequest = app(NovaRequest::class);
                $model = $novaRequest->findResource()->resource;
                $localeColumn = LinkableFacade::getModelLocaleColumn($model);
                if ($localeColumn !== null) {
                    $locale = $model->{$localeColumn};
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
