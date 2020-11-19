<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Grammar type manager
    |--------------------------------------------------------------------------
    |
    | This option defines the class that will manage all grammar types.
    |
    */

    'manager' => Laramore\Grammars\GrammarTypeManager::class,

    /*
    |--------------------------------------------------------------------------
    | Default Grammar namespace
    |--------------------------------------------------------------------------
    |
    | This option defines the namespace used as base for all grammar classes.
    |
    */

    'namespace' => 'Illuminate\Database\Schema\Grammars',

    /*
    |--------------------------------------------------------------------------
    | Grammar classes
    |--------------------------------------------------------------------------
    |
    | All grammar classes to handle, where to add custom definitions.
    |
    | Supported: 'automatic', 'base' (add to all grammar classes), 'disabled', array of classe names.
    |
    */

    'configurations' => 'automatic',

];
