<?php
/**
 * Interact with number fields.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Traits\Field;

use Laramore\Elements\TypeElement;
use Laramore\Facades\{
    Option, Type
};

trait NumberInteraction
{
    /**
     * Return the type of the field.
     *
     * @return TypeElement
     */
    public function getType(): TypeElement
    {
        if ($this->hasOption(Option::unsigned())) {
            if ($this->hasOption(Option::bigNumber())) {
                return Type::get($this->getConfig('types.big_unsigned'));
            } else if ($this->hasOption(Option::smallNumber())) {
                return Type::get($this->getConfig('types.small_unsigned'));
            }

            return Type::get($this->getConfig('types.unsigned'));
        } else if ($this->hasOption(Option::bigNumber())) {
            return Type::get($this->getConfig('types.big'));
        } else if ($this->hasOption(Option::smallNumber())) {
            return Type::get($this->getConfig('types.small'));
        }

        return $this->resolveType();
    }

    /**
     * Force the value to be unsigned or not, positive or not.
     *
     * @param boolean $unsigned
     * @param boolean $positive
     * @return self
     */
    public function unsigned(bool $unsigned=true, bool $positive=true)
    {
        $this->needsToBeUnlocked();

        if ($unsigned) {
            if ($positive) {
                return $this->positive();
            }

            return $this->negative();
        }

        $this->removeOption(Option::negative());
        $this->removeOption(Option::unsigned());

        return $this;
    }

    /**
     * Force the value to be positive.
     *
     * @return self
     */
    public function positive()
    {
        $this->needsToBeUnlocked();

        $this->addOption(Option::unsigned());
        $this->removeOption(Option::negative());

        return $this;
    }

    /**
     * Force the value to be negative.
     *
     * @return self
     */
    public function negative()
    {
        $this->needsToBeUnlocked();

        $this->addOption(Option::negative());

        return $this;
    }

    /**
     * Cast the value to correspond to the field desire.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function cast($value)
    {
        if (is_null($value)) {
            return $value;
        }

        if ($this->hasOption(Option::unsigned())) {
            $newValue = abs($value);

            if ($this->hasOption(Option::negative())) {
                $newValue = (- $newValue);
            }

            $value = $newValue;
        }

        return $value;
    }
}
