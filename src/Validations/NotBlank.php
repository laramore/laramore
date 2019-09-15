<?php
/**
 * Validate that the value is not blank/empty.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Validations;

class NotBlank extends Validation
{
    public function isValueValid($value): bool
    {
        return !empty(trim($value));
    }

    public function getMessage()
    {
        return "This field cannot be blank.";
    }
}
