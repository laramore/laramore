<?php
/**
 * Define a unique field contract.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Contracts\Field\Constraint;

use Laramore\Contracts\Field\Field;
use Laramore\Fields\Constraint\UniqueConstraintHandler;

interface UniqueField extends Field
{
    /**
     * Return the relation handler for this meta.
     *
     * @return UniqueConstraintHandler
     */
    public function getConstraintHandler(): UniqueConstraintHandler;
}
