<?php
/**
 * Define a basic validation rule.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Validations;

class NotNullable extends Validation
{
    /**
     * An observer needs at least a name and a \Closure.
     *
     * @param mixed   $field
     * @param integer $priority
     */
    public function __construct($field, int $priority=self::MAX_PRIORITY)
    {
        parent::__construct($field, $priority);
    }

    public function isValueValid($value): bool
    {
        return !is_null($value);
    }

    public function getMessage()
    {
        return 'This field cannot be null.';
    }
}
