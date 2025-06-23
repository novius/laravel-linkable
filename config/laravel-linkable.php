<?php

return [
    // Laravel Linkable will autoload all Model using Linkable trait in this directory
    'autoload_models_in' => app_path('Models'),

    // The guard name to preview a model without using the preview token
    'guard_preview' => null,

    /*
     * Sometimes you need to link items that are not objects.
     *
     * This config allows you to link routes.
     *     For instance: 'contact' => 'page.contact'
     *
     * "contact" will be the parameter of the laravel function route().
     * "page.contact" will be the parameter of the laravel function trans().
     */
    'linkable_routes' => [],

    /*
     * If you want to add specific models that use the linkable trait and that do not appear in your `autoload_models_in` directory
     */
    'linkable_models' => [
        // App\Models\Page::class,
    ],

    // Set to `true` if you don't want to use localization
    'disable_localization' => false,
];
