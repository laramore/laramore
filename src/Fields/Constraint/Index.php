<?php
/**
 * Define a index constraint.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields\Constraint;

class Index extends BaseIndexableConstraint
{
    /**
     * Return the constraint name.
     *
     * @return string
     */
    public function getConstraintType(): string
    {
        return static::INDEX;
    }
}
