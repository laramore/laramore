<?php
/**
 * Validate that the value is not zero.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Validations;

class NotZero extends Validation
{
    public function isValueValid($value): bool
    {
        return $value !== 0;
    }

    public function getMessage()
    {
        return "The value cannot be equal to 0.";
    }
}
