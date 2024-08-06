<?php

namespace Novius\LaravelLinkable\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Novius\LaravelLinkable\Tests\Models\LinkableModel;

class LinkableModelFactory extends Factory
{
    protected $model = LinkableModel::class;

    public function contextDefault(): LinkableModelFactory
    {
        return $this->state(function () {
            return [
                'context' => 'default',
            ];
        });
    }

    public function published(): LinkableModelFactory
    {
        return $this->state(function () {
            return [
                'published' => true,
            ];
        });
    }

    public function definition(): array
    {
        return [
            'title' => fake()->title(),
            'description' => fake()->text(),
            'context' => fake()->word(),
        ];
    }
}
