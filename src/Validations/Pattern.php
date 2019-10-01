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

class Pattern extends Validation
{
    protected $pattern;
    protected $flags;
    protected $type;

    public function isValueValid($value): bool
    {
        return \preg_match($this->pattern, $value, $_, $this->flags) === 1;
    }

    public function getMessage()
    {
        return "This field does not correspond to a valid $this->type.";
    }
}
