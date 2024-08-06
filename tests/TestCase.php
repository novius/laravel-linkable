<?php

namespace Novius\LaravelLinkable\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Novius\LaravelLinkable\LaravelLinkableServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            static function (string $modelName) {
                return 'Novius\\LaravelLinkable\\Tests\\Database\\Factories\\'.class_basename($modelName).'Factory';
            }
        );

        $this->setUpDatabase($this->app);
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelLinkableServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUpDatabase($app): void
    {
        $this->loadLaravelMigrations();

        $app['db']
            ->connection()
            ->getSchemaBuilder()
            ->create('linkable_models', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
                $table->text('description');
                $table->string('context');
                $table->string('preview_token');
                $table->boolean('published')->default(false);
                $table->timestamps();
            });
    }
}
