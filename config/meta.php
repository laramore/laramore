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

    'manager' => Laramore\Eloquent\MetaManager::class,

    /*
    |--------------------------------------------------------------------------
    | Default Meta class
    |--------------------------------------------------------------------------
    |
    | This option defines the default meta class.
    |
    */

    'class' => Laramore\Eloquent\Meta::class,

    /*
    |--------------------------------------------------------------------------
    | Default Meta pivot class
    |--------------------------------------------------------------------------
    |
    | This option defines the default meta pivot class.
    |
    */

	'pivot_class' => Laramore\Eloquent\PivotMeta::class,

    /*
    |--------------------------------------------------------------------------
    | Default builder class
    |--------------------------------------------------------------------------
    |
    | This option defines the default meta class.
    |
    */

    'builder_class' => Laramore\Eloquent\Builder::class,

    /*
    |--------------------------------------------------------------------------
    | Default collection class
    |--------------------------------------------------------------------------
    |
    | This option defines the default collection class.
    |
    */

    'collection_class' => Laramore\Eloquent\ModelCollection::class,

    /*
    |--------------------------------------------------------------------------
    | Models namespace
    |--------------------------------------------------------------------------
    |
    | This option defines the namespace used as base for all metas.
    |
    */

    'models_namespace' => 'App\\Models',

    /*
    |--------------------------------------------------------------------------
    | Pivotrs namespace
    |--------------------------------------------------------------------------
    |
    | This option defines the namespace used as base for all metas.
    |
    */

    'pivots_namespace' => 'App\\Pivots',

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
