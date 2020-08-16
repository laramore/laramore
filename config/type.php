<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Type manager
    |--------------------------------------------------------------------------
    |
    | This option defines the class that will manage all types.
    |
    */

    'manager' => Laramore\Elements\TypeManager::class,

    /*
    |--------------------------------------------------------------------------
    | Default types
    |--------------------------------------------------------------------------
    |
    | This option defines the default types used by fields.
    | A field has a type. The type describes its default options,
    | its required type value. Also, other packages can define
    | the field factory type, its migration type, etc.
    |
    */

    'configurations' => [
        'binary' => [
            'native' => 'binary',
            'default_options' => [
                'fillable', 'required',
            ],
        ],
        'boolean' => [
            'native' => 'bool',
            'default_options' => [
                'visible', 'fillable', 'required',
            ],
        ],
        'composed' => [
            'native' => 'composed',
            'default_options' => [
                'visible', 'fillable', 'required',
            ],
        ],
        'date_time' => [
            'native' => 'date time',
            'default_options' => [
                'visible', 'fillable', 'required',
            ],
        ],
        'decimal' => [
            'native' => 'decimal',
            'default_options' => [
                'visible', 'fillable', 'required',
            ],
        ],
        'enum' => [
            'native' => 'enum',
            'default_options' => [
                'visible', 'fillable', 'required',
            ],
        ],
        'integer' => [
            'native' => 'integer',
            'default_options' => [
                'visible', 'fillable', 'required',
            ],
        ],
        'json' => [
            'native' => 'json',
            'default_options' => [
                'visible', 'fillable', 'required',
            ],
        ],
        'relation' => [
            'native' => 'relation',
            'default_options' => [
                'visible', 'fillable', 'required',
            ],
        ],
        'reversed_relation' => [
            'native' => 'reversed relation',
            'default_options' => [
                'visible', 'fillable',
            ],
        ],
        'text' => [
            'native' => 'text',
            'default_options' => [
                'visible', 'fillable', 'required', 'not_blank',
            ],
        ],
        'timestamp' => [
            'native' => 'timestamp',
            'default_options' => [
                'visible', 'fillable', 'required',
            ],
        ],

        'char' => [
            'native' => 'char',
            'parent' => 'text',
        ],

        'pattern' => [
            'native' => 'pattern',
            'parent' => 'char',
        ],

        'email' => [
            'native' => 'email',
            'parent' => 'pattern',
        ],
        'uri' => [
            'native' => 'uri',
            'parent' => 'pattern',
        ],
        'password' => [
            'native' => 'password',
            'parent' => 'pattern',
            'default_options' => [
                'fillable', 'required', 'need_lowercase', 'need_uppercase', 'need_number'
            ],
        ],

        'unsigned_integer' => [
            'native' => 'unsigned integer',
            'parent' => 'integer',
            'default_options' => [
                'visible', 'fillable', 'required', 'unsigned',
            ],
        ],
        'big_integer' => [
            'native' => 'unsigned integer',
            'parent' => 'integer',
            'default_options' => [
                'visible', 'fillable', 'required', 'big',
            ],
        ],
        'small_integer' => [
            'native' => 'unsigned integer',
            'parent' => 'integer',
            'default_options' => [
                'visible', 'fillable', 'required', 'small',
            ],
        ],
        'big_unsigned_integer' => [
            'native' => 'unsigned integer',
            'parent' => 'integer',
            'default_options' => [
                'visible', 'fillable', 'required', 'unsigned', 'big',
            ],
        ],
        'small_unsigned_integer' => [
            'native' => 'unsigned integer',
            'parent' => 'unsigned_integer',
            'default_options' => [
                'visible', 'fillable', 'required', 'unsigned', 'small',
            ],
        ],
        'big_unsigned_integer' => [
            'native' => 'big unsigned integer',
            'parent' => 'unsigned_integer',
            'default_options' => [
                'visible', 'fillable', 'required', 'unsigned', 'big_number',
            ],
        ],
        'big_integer' => [
            'native' => 'big integer',
            'parent' => 'integer',
            'default_options' => [
                'visible', 'fillable', 'required', 'big_number',
            ],
        ],

        'increment' => [
            'native' => 'increment',
            'parent' => 'unsigned_integer',
            'default_options' => [
                'visible', 'not_zero', 'unsigned', 'require_sign',
            ],
        ],
        'primary_id' => [
            'native' => 'primary id',
            'parent' => 'unsigned_integer',
            'default_options' => [
                'visible', 'not_zero', 'unsigned', 'require_sign', 'not_nullable',
            ],
        ],

        'big_increment' => [
            'native' => 'big increment',
            'parent' => 'increment',
            'default_options' => [
                'visible', 'not_zero', 'unsigned', 'require_sign', 'big_number',
            ],
        ],
        
        
        'unsigned_decimal' => [
            'native' => 'unsigned decimal',
            'parent' => 'decimal',
            'default_options' => [
                'visible', 'fillable', 'required', 'unsigned',
            ],
        ],
        'big_decimal' => [
            'native' => 'unsigned decimal',
            'parent' => 'decimal',
            'default_options' => [
                'visible', 'fillable', 'required', 'big',
            ],
        ],
        'small_decimal' => [
            'native' => 'unsigned decimal',
            'parent' => 'decimal',
            'default_options' => [
                'visible', 'fillable', 'required', 'small',
            ],
        ],
        'big_unsigned_decimal' => [
            'native' => 'unsigned decimal',
            'parent' => 'unsigned_decimal',
            'default_options' => [
                'visible', 'fillable', 'required', 'unsigned', 'big',
            ],
        ],
        'small_unsigned_decimal' => [
            'native' => 'unsigned decimal',
            'parent' => 'unsigned_decimal',
            'default_options' => [
                'visible', 'fillable', 'required', 'unsigned', 'small',
            ],
        ],
        
    ],

];
