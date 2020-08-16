<?php
/**
 * Define a relation constraint contract.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Contracts\Field\Constraint;

interface RelationalConstraint extends Constraint
{
    /**
     * Return the attributes that points to.
     *
     * @return IndexableConstraint
     */
    public function getTarget(): IndexableConstraint;
}
