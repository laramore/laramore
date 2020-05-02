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

    'configurations' => [
        'get' => [
            'name_template' => 'get^{fieldname}Attribute',
            'requirements' => ['instance'],
            'multi_name' => false,
        ],
        'set' => [
            'name_template' => 'set^{fieldname}Attribute',
            'requirements' => ['instance'],
        ],
        'reset' => [
            'name_template' => 'reset^{fieldname}Attribute',
            'requirements' => ['instance'],
        ],
    ],

];
