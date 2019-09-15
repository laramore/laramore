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

use Laramore\Type;

class Typed extends Validation
{
    protected $type;

    public function isValueValid($value): bool
    {
        return $this->getType()->isType($value);
    }

    public function getMessage()
    {
        return "This field must be a valid {$this->getType()->native}.";
    }

    public function setType(Type $type)
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): Type
    {
        return $this->type;
    }
}
