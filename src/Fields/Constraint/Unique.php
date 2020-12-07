<?php
/**
 * Define a unique constraint.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields\Constraint;

class Unique extends BaseIndexableConstraint
{
    /**
     * Return the constraint name.
     *
     * @return string
     */
    public function getConstraintType(): string
    {
        return static::UNIQUE;
    }
}
