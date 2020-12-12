<?php

namespace Laramore\Fields;

use Illuminate\Support\Facades\Schema;

return [
    
    /*
    |--------------------------------------------------------------------------
    | Default fields
    |--------------------------------------------------------------------------
    |
    | This option defines the default fields.
    |
    */

    Binary::class => [
        'options' => [
            'fillable', 'required',
        ],
    ],
    Boolean::class => [
        'options' => [
            'visible', 'fillable', 'required',
        ],
    ],
    Char::class => [
        'options' => [
            'visible', 'fillable', 'required',
        ],
        'max_length' => Schema::getFacadeRoot()::$defaultStringLength,
    ],
    DateTime::class => [
        'options' => [
            'visible', 'fillable', 'required',
        ],
        'format' => 'Y-m-d H:i:s',
    ],
    Decimal::class => [
        'options' => [
            'visible', 'fillable', 'required',
        ],
        'total_digits' => 8,
        'decimal_digits' => 2,
    ],
    Email::class => [
        'options' => [
            'visible', 'fillable', 'required',
        ],
        'max_length' => Schema::getFacadeRoot()::$defaultStringLength,
        'patterns' => [
            'username' => '/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*/iD',
            'domain' => '/^(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD',
            'email' => '/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD',
            'separator' => '@',
            'flags' => null,
        ],
    ],
    Enum::class => [
        'options' => [
            'visible', 'fillable', 'required',
        ],
    ],
    Increment::class => [
        'options' => [
            'visible', 'fillable', 'required',
        ],
        'step' => 1,
    ],
    Integer::class => [
        'options' => [
            'visible', 'fillable', 'required',
        ],
    ],
    Json::class => [
        'options' => [
            'visible', 'fillable', 'required',
        ],
    ],
    ManyToMany::class => [
        'options' => [
            'visible', 'fillable',
        ],
        'pivot_namespace' => 'App\\Pivots',
        'fields' => [
            'reversed' => Reversed\BelongsToMany::class,
        ],
        'templates' => [
            'reversed' => '+{modelname}',
            'pivot' => 'pivot',
            'reversed_pivot' => 'pivot',
            'self_reversed' => 'reversed_+{name}',
            'self_reversed_pivot' => 'reversed_+{modelname}',
        ],
    ],
    ManyToOne::class => [
        'options' => [
            'visible', 'fillable', 'required',
        ],
        'fields' => [
            'id' => Integer::class,
            'reversed' => Reversed\HasMany::class,
        ],
        'templates' => [
            'id' => '${name}_${identifier}',
            'reversed' => '+{modelname}',
            'self_reversed' => 'reversed_+{name}',
        ],
    ],
    OneToOne::class => [
        'options' => [
            'visible', 'fillable', 'required',
        ],
        'fields' => [
            'id' => UniqueId::class,
            'reversed' => Reversed\HasOne::class,
        ],
        'templates' => [
            'id' => '${name}_${identifier}',
            'reversed' => '${modelname}',
            'self_reversed' => 'reversed_+{name}',
        ],
    ],
    Password::class => [
        'options' => [
            'fillable', 'required', 'need_lowercase', 'need_uppercase', 'need_number'
        ],
        'max_length' => 60, // Length required for hashs.
        'min_length' => 8, // Min length of any password.
        'patterns' => [
            'min_max_part' => '(?=\S{$min,$max})',
            'one_lowercase_part' => '(?=\S*[a-z])',
            'one_uppercase_part' => '(?=\S*[A-Z])',
            'one_number_part' => '(?=\S*[\d])',
            'one_special_part' => '(?=\S*[\W])',
        ],
    ],
    PrimaryId::class => [
        'options' => [
            'visible', 'fillable', 'required', 'unsigned',
        ],
        'step' => 1,
    ],
    Reversed\BelongsToMany::class => [
        'options' => [
            'visible', 'fillable',
        ],
    ],
    Reversed\HasMany::class => [
        'options' => [
            'visible', 'fillable',
        ],
    ],
    Reversed\HasOne::class => [
        'options' => [
            'visible', 'fillable',
        ],
    ],
    UniqueId::class => [
        'options' => [
            'visible', 'fillable', 'required',
        ],
    ],
    Text::class => [
        'options' => [
            'visible', 'fillable', 'required', 'not_blank',
        ],
    ],
    Uri::class => [
        'options' => [
            'visible', 'fillable', 'required',
        ],
        'max_length' => Schema::getFacadeRoot()::$defaultStringLength,
        'patterns' => [
            'identifier' => '/^\S+$/',
            'protocol' => '/^\S+:\/{0,2}$/',
            'uri' => '/^\S+:\/{0,2}\S+$/',
            'flags' => null,
        ],
    ],

];
