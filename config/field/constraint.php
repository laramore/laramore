<?php

namespace Laramore\Fields\Constraint;

return [

    /*
    |--------------------------------------------------------------------------
    | Default constraints
    |--------------------------------------------------------------------------
    |
    | This option defines the default constraints used in fields.
    |
    */

    'manager' => ConstraintManager::class,

    'classes' => [
        BaseIndexableConstraint::PRIMARY => Primary::class,
        BaseIndexableConstraint::INDEX => Index::class,
        BaseIndexableConstraint::UNIQUE => Unique::class,
        BaseRelationalConstraint::FOREIGN => Foreign::class,
    ],

    'configurations' => [
        'primary' => [
            'type' => BaseIndexableConstraint::PRIMARY,
        ],
        'index' => [
            'type' => BaseIndexableConstraint::INDEX,
        ],
        'unique' => [
            'type' => BaseIndexableConstraint::UNIQUE,
        ],
        'foreign' => [
            'type' => BaseRelationalConstraint::FOREIGN,
        ],
    ],
];
