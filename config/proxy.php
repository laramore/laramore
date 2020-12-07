<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default proxies
    |--------------------------------------------------------------------------
    |
    | These options define all proxy configurations.
    |
    */

    'manager' => \Laramore\Proxies\ProxyManager::class,

    'class' => \Laramore\Proxies\Proxy::class,

    'field_class' => \Laramore\Proxies\FieldProxy::class,

    'multi_class' => \Laramore\Proxies\MultiProxy::class,

    'templates' => [
        'name' => '-{methodname}-^{identifier}',
        'multi_name' => '-{methodname}',
    ],

    'configurations' => [

    ],

    'field_configurations' => [
        'cast' => [
            'static' => true,
        ],
        'serialize' => [
            'static' => true,
        ],
        'get_default' => [
            'static' => true,
        ],
        'get' => [
            'allow_multi' => false,
            'templates' => [
                'name' => '-{methodname}-^{identifier}Attribute',
            ],
        ],
        'set' => [
            'allow_multi' => false,
            'templates' => [
                'name' => '-{methodname}-^{identifier}Attribute',
            ],
        ],
        'reset' => [
            'templates' => [
                'name' => '-{methodname}-^{identifier}Attribute',
                'multi_name' => '-{methodname}Attribute',
            ],
        ],
        'relate' => [
            'templates' => [
                'name' => '-{identifier}',
            ],
        ],
        'where' => [
            'allow_multi' => false,
            'templates' => [
                'name' => 'scope-^{methodname}-^{identifier}',
            ],
        ],
        'doesnt_have' => [
            'templates' => [
                'name' => 'scope-^{methodname}-^{identifier}',
                'multi_name' => 'scope-^{methodname}',
            ],
        ],
        'has' => [
            'templates' => [
                'name' => 'scope-^{methodname}-^{identifier}',
                'multi_name' => 'scope-^{methodname}',
            ],
        ],
    ],
];
