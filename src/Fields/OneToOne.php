<?php
/**
 * Define a one to one field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

use Laramore\Traits\Field\OneToOneRelation;

class OneToOne extends CompositeField
{
    use OneToOneRelation;

    protected static $defaultLinks = [
        'reversed' => HasOne::class,
    ];
}
