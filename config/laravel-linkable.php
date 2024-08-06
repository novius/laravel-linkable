<?php

return [
    'autoload_models_in' => app_path('Models'),

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
     * Entities linkable by a menu item.
     *
     * Warning: The models listed below must use the trait \Novius\LaravelLinkable\Traits\Linkable.
     */

    'linkable_models' => [
        //App\Models\Page::class,
    ],
];
