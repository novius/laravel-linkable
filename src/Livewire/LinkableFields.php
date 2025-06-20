<?php

namespace Novius\LaravelLinkable\Livewire;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Split;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use Novius\LaravelLinkable\Facades\Linkable as LinkableFacade;
use Novius\LaravelLinkable\Traits\Linkable;

/**
 * @property-read Form $form
 */
class LinkableFields extends Component implements HasForms
{
    use InteractsWithForms;

    public array $linkableClasses = [];

    public ?string $initState = null;

    public ?array $data = [];

    public function mount(): void
    {
        if ($this->initState !== null) {
            $infos = explode(':', $this->initState);
            if (count($infos) >= 2) {
                $this->data = ['group' => $infos[0], 'item' => $infos[1], 'locale' => $infos[2] ?? null];
            }
        }

        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Split::make([
                    Select::make('group')
                        ->label('')
                        ->placeholder(trans('laravel-linkable::linkable.placeholder_group'))
                        ->options(LinkableFacade::groups($this->linkableClasses))
                        ->grow(false)
                        ->reactive(),
                    Select::make('item')
                        ->label('')
                        ->placeholder(trans('laravel-linkable::linkable.placeholder_item'))
                        ->searchable()
                        ->getSearchResultsUsing(function (Get $get, string $search) {
                            $group = $get('group');
                            $locale = $get('locale');
                            if ($group === null) {
                                return [];
                            }
                            if ($group === 'route') {
                                return LinkableFacade::links(['route'])
                                    ->filter(fn ($item) => Str::contains($item['label'], $search))
                                    ->mapWithKeys(fn ($item) => [
                                        $item['type'].':'.$item['id'] => $item['label'],
                                    ])
                                    ->toArray();
                            }

                            /** @var Model&Linkable $model */
                            $model = new $group;
                            $config = $model->linkableConfig();
                            if ($config === null) {
                                return [];
                            }

                            $query = $config->optionsQuery ? call_user_func($config->optionsQuery,
                                $model::query()) : $model::query();
                            if ($locale !== null && LinkableFacade::getModelLocaleColumn(__CLASS__) !== null) {
                                $query->withLocale($locale);
                            }
                            $query->where(function (Builder $query) use ($search, $config) {
                                foreach ($config->optionSearch as $column) {
                                    $query->orWhere($column, 'like', "%{$search}%");
                                }
                            });

                            return $query->get()
                                ->mapWithKeys(function (Model $item) use ($config) {
                                    $label = is_callable($config->optionLabel) ? call_user_func($config->optionLabel, $item) : $item->{$config->optionLabel};

                                    return [$item->{$item->getKeyName()} => $label];
                                })
                                ->toArray();
                        })
                        ->getOptionLabelUsing(function (Get $get, $value) {
                            $group = $get('group');
                            if ($group === null) {
                                return null;
                            }
                            if ($group === 'route') {
                                return LinkableFacade::links(['route'])
                                    ->firstWhere('id', $value)
                                    ?->label;
                            }

                            /** @var Model&Linkable $model */
                            $model = new $group;
                            $config = $model->linkableConfig();
                            if ($config === null) {
                                return null;
                            }

                            $model = $model::query()->find($value);
                            if ($model === null) {
                                return null;
                            }

                            return is_callable($config->optionLabel) ? call_user_func($config->optionLabel, $model) : $model->{$config->optionLabel};
                        })
                        ->afterStateUpdated(function (Get $get, $state) {
                            $item = implode(':', array_filter([$get('group'), $state, $get('locale')]));
                            $this->dispatch('linkable-selected', item: $item);
                        })
                        ->reactive(),
                    Select::make('locale')
                        ->label('')
                        ->placeholder(trans('laravel-linkable::linkable.placeholder_locale'))
                        ->grow(false)
                        ->reactive(),
                ]),
            ])
            ->statePath('data');
    }

    public function render(): View
    {
        return view('laravel-linkable::linkable-fields');
    }
}
