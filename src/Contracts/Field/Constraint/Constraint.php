<?php
/**
 * Define a classic constraint contract.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Contracts\Field\Constraint;

use Laramore\Contracts\Field\AttributeField;
use Laramore\Contracts\Field\Field;
use Laramore\Contracts\Locked;

interface Constraint extends Locked
{
    /**
     * Return the constraint name.
     *
     * @return string
     */
    public function getConstraintType(): string;

    /**
     * Return all concerned fields.
     *
     * @return array
     */
    public function getFields(): array;

    /**
     * Return concerned field.
     *
     * @return Field
     */
    public function getField(): Field;

    /**
     * Return all concerned attribute fields.
     *
     * @return array
     */
    public function getAttributes(): array;

    /**
     * Return concerned attribute field.
     *
     * @return AttributeField
     */
    public function getAttribute(): AttributeField;

    /**
     * Indicate if this constraint is composed of multiple fields.
     *
     * @return boolean
     */
    public function isComposed(): bool;
}
