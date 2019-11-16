<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Meta manager
    |--------------------------------------------------------------------------
    |
    | This option defines the class that will manage all metas.
    |
    */

    'manager' => Laramore\MetaManager::class,

    /*
    |--------------------------------------------------------------------------
    | Default Meta class
    |--------------------------------------------------------------------------
    |
    | This option defines the default meta class.
    |
    */

	'class' => Laramore\Meta::class,

    /*
    |--------------------------------------------------------------------------
    | Default Meta namespace
    |--------------------------------------------------------------------------
    |
    | This option defines the namespace used as base for all metas.
    |
    */

	'namespace' => 'App\\Models',

    /*
    |--------------------------------------------------------------------------
    | All metas to generate
    |--------------------------------------------------------------------------
    |
    | This option defines the classes to generate metas.
    |
    */

	'configurations' => 'automatic',
];
