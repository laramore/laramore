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

use Illuminate\Database\Eloquent\Model;

class NotZero extends Validation
{
    public function isValueValid(Model $model, $value): bool
    {
        return (int) $value !== 0;
    }

    public function getMessage()
    {
        return "The value cannot be equal to 0.";
    }
}
