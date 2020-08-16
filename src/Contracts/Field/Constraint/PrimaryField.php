<?php
/**
 * Define a primary field contract.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Contracts\Field\Constraint;

use Laramore\Contracts\Field\Field;
use Laramore\Fields\Constraint\PrimaryConstraintHandler;

interface PrimaryField extends Field
{
    /**
     * Return the relation handler for this meta.
     *
     * @return PrimaryConstraintHandler
     */
    public function getConstraintHandler(): PrimaryConstraintHandler;
}
