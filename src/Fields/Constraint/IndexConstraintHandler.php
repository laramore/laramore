<?php
/**
 * Handle all constraints adding a unique constraints during creation.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Fields\Constraint;

use Laramore\Contracts\Field\Field;

class IndexConstraintHandler extends FieldConstraintHandler
{
    /**
     * Create a field handler for a specific field.
     *
     * @param Field $field
     */
    public function __construct(Field $field)
    {
        parent::__construct($field);

        $this->create(BaseIndexableConstraint::INDEX, null, [$field]);
    }
}
