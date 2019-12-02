<?php

/*
|--------------------------------------------------------------------------
| Shared proxy for all fields
|--------------------------------------------------------------------------
|
| The proxy get has configurations.
|
*/

return [
    'name_template' => 'get^{fieldname}Attribute',
    'requirements' => ['instance'],
];
