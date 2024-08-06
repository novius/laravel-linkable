<?php

namespace Novius\LaravelLinkable\Tests\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Novius\LaravelLinkable\Configs\LinkableConfig;
use Novius\LaravelLinkable\Traits\Linkable;

/**
 * @property int id
 * @property string title
 * @property string description
 * @property string context
 * @property ?string preview_token
 * @property bool published
 */
class LinkableModel extends Model
{
    use HasFactory;
    use HasTimestamps;
    use Linkable;

    protected $table = 'linkable_models';

    protected $guarded = [];

    protected $attributes = [
        'published' => false,
    ];

    protected static function booted(): void
    {
        static::saving(static function (LinkableModel $model) {
            if (empty($model->preview_token)) {
                $model->preview_token = Str::random();
            }
        });
    }

    public function linkableConfig(): ?LinkableConfig
    {
        return new LinkableConfig(
            routeName: 'model',
            routeParameterName: 'model',
            optionLabel: 'title',
            optionGroup: 'Model',
            resolveQuery: function (Builder|LinkableModel $query) {
                $query->where('context', 'default');
            },
            resolveNotPreviewQuery: function (Builder|LinkableModel $query) {
                $query->where('published', true);
            },
            previewTokenField: 'preview_token'
        );
    }
}
