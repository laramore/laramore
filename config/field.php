<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Name templates for the fields generation
    |--------------------------------------------------------------------------
    |
    | This option defines the template used to generate the name and the
    | attribute name, if existant, of a field.
    |
    */

    'templates' => [
        'name' => '_{name}',
        'attname' => '_{attname}',
        'method_owner' => '-{methodname}FieldValue',
    ],

];
