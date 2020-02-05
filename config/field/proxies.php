<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Common proxies for fields with metas
    |--------------------------------------------------------------------------
    |
    | This option defines all proxies used for metas.
    |
    */

    'common' => [
        'transform' => [],
        'serialize' => [],
        'dry' => [],
        'cast' => [],
        'default' => [],
        'getValue' => [
            'name_template' => 'get^{fieldname}Attribute',
            'requirements' => ['instance'],
        ],
        'setValue' => [
            'name_template' => 'set^{fieldname}Attribute',
            'requirements' => ['instance'],
        ],
        'resetValue' => [
            'name_template' => 'reset^{fieldname}Attribute',
            'requirements' => ['instance'],
        ],
    ],

];
