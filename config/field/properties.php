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
        'proxy' => [
            'configurations' => [
                'dry' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
                'hydrate' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
                'hydrate' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
            ],
        ],
    ],
    Boolean::class => [
        'options' => [
            'visible', 'fillable', 'required',
        ],
        'proxy' => [
            'configurations' => [
                'dry' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
                'hydrate' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
                'is' => [
                    'needs_value' => true,
                ],
                'is_not' => [
                    'needs_value' => true,
                ],
            ],
        ],
    ],
    Char::class => [
        'options' => [
            'visible', 'fillable', 'required',
        ],
        'max_length' => Schema::getFacadeRoot()::$defaultStringLength,
        'proxy' => [
            'configurations' => [
                'dry' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
                'hydrate' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
                'resize' => [],
            ],
        ],
    ],
    DateTime::class => [
        'format' => 'Y-m-d H:i:s',
        'options' => [
            'visible', 'fillable', 'required',
        ],
        'proxy' => [
            'configurations' => [
                'dry' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
                'hydrate' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
            ],
        ],
    ],
    Decimal::class => [
        'total_digits' => 8,
        'decimal_digits' => 2,
        'options' => [
            'visible', 'fillable', 'required',
        ],
        'proxy' => [
            'configurations' => [
                'dry' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
                'hydrate' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
            ],
        ],
    ],
    Email::class => [
        'max_length' => Schema::getFacadeRoot()::$defaultStringLength,
        'proxy' => [
            'configurations' => [
                'dry' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
                'hydrate' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
                'fix' => [],
            ],
        ],
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
        'elements_proxy' => [
            'class' => \Laramore\Proxies\EnumProxy::class,
            'configurations' => [
                'is' => [
                    'templates' => [
                        'name' => '-{methodname}-^{elementname}',
                    ],
                    'needs_value' => true,
                ],
            ],
        ],
        'proxy' => [
            'configurations' => [
                'dry' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
                'hydrate' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
                'get_elements' => [
                    'templates' => [
                        'name' => 'get-^{identifier}Elements',
                    ],
                ],
                'get_elements_value' => [
                    'templates' => [
                        'name' => 'get+^{identifier}',
                    ],
                ],
                'is' => [
                    'needs_value' => true,
                ],
                'is_not' => [
                    'needs_value' => true,
                ],
            ],
        ],
    ],
    Increment::class => [
        'options' => [
            'visible', 'fillable', 'required',
        ],
        'step' => 1,
        'proxy' => [
            'configurations' => [
                'dry' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
                'hydrate' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
                'increment' => [],
                'decrement' => [],
            ],
        ],
    ],
    Integer::class => [
        'options' => [
            'visible', 'fillable', 'required',
        ],
        'proxy' => [
            'configurations' => [
                'dry' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
                'hydrate' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
            ],
        ],
    ],
    Json::class => [
        'options' => [
            'visible', 'fillable', 'required',
        ],
        'proxy' => [
            'configurations' => [
                'dry' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
                'hydrate' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
            ],
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
        'proxy' => [
            'configurations' => [
                'retrieve' => [],
                'attach' => [],
                'update' => [],
                'delete' => [],
            ],
        ],
    ],
    ManyToOne::class => [
        'fields' => [
            'id' => Integer::class,
            'reversed' => Reversed\HasMany::class,
        ],
        'options' => [
            'visible', 'fillable', 'required',
        ],
        'templates' => [
            'id' => '${name}_${identifier}',
            'reversed' => '+{modelname}',
            'self_reversed' => 'reversed_+{name}',
        ],
        'proxy' => [
            'configurations' => [
                'retrieve' => [],
                'attach' => [],
                'detach' => [],
                'sync' => [],
                'update' => [],
                'delete' => [],
                'toggle' => [],
                'sync_without_detaching' => [],
                'update_existing_pivot' => [],
            ],
        ],
    ],
    OneToOne::class => [
        'fields' => [
            'id' => UniqueId::class,
            'reversed' => Reversed\HasOne::class,
        ],
        'options' => [
            'visible', 'fillable', 'required',
        ],
        'templates' => [
            'id' => '${name}_${identifier}',
            'reversed' => '${modelname}',
            'self_reversed' => 'reversed_+{name}',
        ],
        'proxy' => [
            'configurations' => [
                'retrieve' => [],
                'attach' => [],
                'detach' => [],
                'sync' => [],
                'update' => [],
                'delete' => [],
                'toggle' => [],
                'sync_without_detaching' => [],
                'update_existing_pivot' => [],
            ],
        ],
    ],
    Password::class => [
        'max_length' => 60, // Length required for hashs.
        'min_length' => 8, // Min length of any password.
        'options' => [
            'fillable', 'required', 'need_lowercase', 'need_uppercase', 'need_number'
        ],
        'proxy' => [
            'configurations' => [
                'dry' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
                'hydrate' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
                'resize' => [],
                'hash' => [],
                'is_correct' => [
                    'templates' => [
                        'name' => 'is$^{identifier}Correct',
                    ],
                    'needs_value' => true,
                ],
            ],
        ],
        'patterns' => [
            'min_max_part' => '(?=\S{$min,$max})',
            'one_lowercase_part' => '(?=\S*[a-z])',
            'one_uppercase_part' => '(?=\S*[A-Z])',
            'one_number_part' => '(?=\S*[\d])',
            'one_special_part' => '(?=\S*[\W])',
        ]
    ],
    PrimaryId::class => [
        'options' => [
            'visible', 'fillable', 'required', 'unsigned',
        ],
        'proxy' => [
            'configurations' => [
                'dry' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
                'hydrate' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
            ],
        ],
    ],
    Reversed\BelongsToMany::class => [
        'options' => [
            'visible', 'fillable',
        ],
        'proxy' => [
            'configurations' => [
                'retrieve' => [],
                'attach' => [],
                'detach' => [],
                'sync' => [],
                'update' => [],
                'delete' => [],
                'toggle' => [],
                'sync_without_detaching' => [],
                'update_existing_pivot' => [],
            ],
        ],
    ],
    Reversed\HasMany::class => [
        'options' => [
            'visible', 'fillable',
        ],
        'proxy' => [
            'configurations' => [
                'retrieve' => [],
                'attach' => [],
                'detach' => [],
                'sync' => [],
                'update' => [],
                'delete' => [],
                'toggle' => [],
                'sync_without_detaching' => [],
                'update_existing_pivot' => [],
            ],
        ],
    ],
    Reversed\HasOne::class => [
        'options' => [
            'visible', 'fillable',
        ],
        'proxy' => [
            'configurations' => [
                'retrieve' => [],
                'attach' => [],
                'detach' => [],
                'sync' => [],
                'update' => [],
                'delete' => [],
                'toggle' => [],
                'sync_without_detaching' => [],
                'update_existing_pivot' => [],
            ],
        ],
    ],
    UniqueId::class => [
        'proxy' => [
            'configurations' => [
                'dry' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
                'hydrate' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
            ],
        ],
    ],
    Text::class => [
        'options' => [
            'visible', 'fillable', 'required', 'not_blank',
        ],
        'proxy' => [
            'configurations' => [
                'dry' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
                'hydrate' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
            ],
        ],
    ],
    Timestamp::class => [
        'format' => 'timestamp',
        'proxy' => [
            'configurations' => [
                'dry' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
                'hydrate' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
            ],
        ],
    ],
    Uri::class => [
        'max_length' => Schema::getFacadeRoot()::$defaultStringLength,
        'proxy' => [
            'configurations' => [
                'dry' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
                'hydrate' => [
                    'static' => true,
                    'allow_multi' => false,
                ],
            ],
        ],
        'patterns' => [
            'identifier' => '/^\S+$/',
            'protocol' => '/^\S+:\/{0,2}$/',
            'uri' => '/^\S+:\/{0,2}\S+$/',
            'flags' => null,
        ]
    ],

];
