<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Option manager
    |--------------------------------------------------------------------------
    |
    | This option defines the class that will manage all options.
    |
    */

    'manager' => Laramore\Elements\OptionManager::class,

    /*
    |--------------------------------------------------------------------------
    | Default options
    |--------------------------------------------------------------------------
    |
    | This option defines the default options used in fields.
    |
    */

    'configurations' => [
        'append' => [
            'description' => 'Append extra value',
            'propagate' => false,
            'adds' => [
                'visible',
            ],
        ],
        'big_number' => [
            'description' => 'Big number value',
            'removes' => [
                'small_number',
            ],
        ],
        'fillable' => [
            'description' => 'Set as fillable',
        ],
        'fixable' => [
            'native' => 'fixable',
            'description' => 'Accept fixable values and auto fix them',
        ],
        'negative' => [
            'description' => 'Force the value to be negative',
            'adds' => [
                'unsigned',
            ],
        ],
        'need_lowercase' => [
            'native' => 'need lowercase',
            'description' => 'Need at least one lowercase caracter',
        ],
        'need_number' => [
            'native' => 'need number',
            'description' => 'Need at least one number caracter',
        ],
        'need_special' => [
            'native' => 'need special',
            'description' => 'Need at least one special caracter',
        ],
        'need_uppercase' => [
            'native' => 'need uppercase',
            'description' => 'Need at least one uppercase caracter',
        ],
        'not_blank' => [
            'native' => 'not blank',
            'description' => 'Forbid blank value',
        ],
        'not_nullable' => [
            'native' => 'not nullable',
            'description' => 'Cannot be nullable',
            'removes' => [
                'nullable',
            ],
        ],
        'not_zero' => [
            'native' => 'not zero',
            'description' => 'Forbid value zero',
        ],
        'nullable' => [
            'description' => 'Nullable value by default',
            'removes' => [
                'not_nullable', 'required',
            ],
        ],
        'require_sign' => [
            'native' => 'require sign',
            'description' => 'Force the value to be of the right sign',
        ],
        'required' => [
            'description' => 'Require a value',
            'adds' => [
                'fillable',
            ],
        ],
        'small_number' => [
            'description' => 'Small number value',
            'removes' => [
                'big_number',
            ],
        ],
        'trim' => [
            'description' => 'Trim value',
        ],
        'unsigned' => [
            'description' => 'Force a value to be unsigned',
        ],
        'use_current' => [
            'native' => 'use current',
            'description' => 'Use the current value',
        ],
        'visible' => [
            'description' => 'Set as visible',
        ],
        'with' => [
            'description' => 'Autoload the relation',
            'propagate' => false,
            'removes' => [
                'with_count',
            ],
        ],
        'with_count' => [
            'native' => 'with count',
            'description' => 'Autoload the number of the relation',
            'propagate' => false,
            'removes' => [
                'with',
            ],
        ],
    ],

];
