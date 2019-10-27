<?php
/**
 * Define a number field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

use Illuminate\Database\Eloquent\Model;
use Laramore\Validations\NotZero;
use Laramore\Elements\Type;
use Types;

class Number extends Field
{
    /**
     * Set of rules.
     * Common to all integer fields.
     *
     * @var integer
     */
    // Indicate the value is unsigned (positive)
    public const UNSIGNED = 512;

    // Indicate the value is positive
    public const POSITIVE = self::UNSIGNED;

    // Indicate the value is negative
    public const NEGATIVITY = 1024;
    public const NEGATIVE = (self::NEGATIVITY | self::UNSIGNED);

    // Except if the sign value is the wrong one
    public const CORRECT_SIGN = 2048;

    // Except if the value is 0
    public const NOT_ZERO = 4096;

    public function getType(): Type
    {
        if ($this->hasRule(self::UNSIGNED)) {
            return Types::unsignedInteger();
        }

        return Types::integer();
    }

    public function unsigned(bool $unsigned=true, bool $positive=true)
    {
        $this->needsToBeUnlocked();

        if ($unsigned) {
            if ($positive) {
                return $this->positive();
            }

            return $this->negative();
        } else {
            // By removing NEGATIVE, we are sure to remove UNSIGNED and NEGATIVITY restriction
            return $this->removeRule(self::NEGATIVE);
        }
    }

    public function positive()
    {
        $this->needsToBeUnlocked();

        $this->addRule(self::POSITIVE);
        $this->removeRule(self::NEGATIVITY);

        return $this;
    }

    public function negative()
    {
        $this->needsToBeUnlocked();

        $this->addRule(self::NEGATIVE);

        return $this;
    }

    protected function setValidations()
    {
        parent::setValidations();

        if ($this->hasRule(self::NOT_ZERO)) {
            $this->setValidation(NotZero::class);
        }
    }

    public function dry($value)
    {
        return is_null($value) ? $value : (int) $value;
    }

    public function cast($value)
    {
        return $this->transform($this->dry($value));
    }

    public function transform($value)
    {
        if (is_null($value)) {
            return $value;
        }

        if ($this->hasRule(self::UNSIGNED)) {
            $newValue = abs($value);

            if ($this->hasRule(self::NEGATIVITY)) {
                $newValue = - $newValue;
            }

            // TODO
            if ($newValue !== $value && $this->hasRule(self::CORRECT_SIGN)) {
                throw new \Exception('The value must be '.($this->hasRule(self::NEGATIVITY) ? 'negative' : 'positive').' for the field `'.$this->name.'`');
            }

            $value = $newValue;
        }

        return $value;
    }

    public function serialize($value)
    {
        return $value;
    }
}
