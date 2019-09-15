<?php
/**
 * Define the length validation rule.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Validations;

class Length extends Validation
{
    protected $minLength;
    protected $maxLength;

    public function isValueValid($value): bool
    {
        $length = strlen($value);

        return (is_null($this->minLength) || $length >= $this->minLength) &&
            (is_null($this->maxLength) || $length <= $this->maxLength);
    }

    public function getMessage()
    {
        $messages = [];

        if (!is_null($this->minLength)) {
            $messages[] = "The length must be at least of $this->minLength.";
        }

        if (!is_null($this->maxLength)) {
            $messages[] = "The length must be at most of $this->maxLength.";
        }

        return $messages;
    }
}
