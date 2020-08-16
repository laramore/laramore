<?php
/**
 * Define an indexable constraint contract.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Contracts\Field\Constraint;

interface IndexableConstraint extends Constraint
{
    /**
     * Return the model class used for this constraint.
     *
     * @return string
     */
    public function getModelClass(): string;
}
