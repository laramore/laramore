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

use Laramore\Facades\Option;

trait NumberInteraction
{
    /**
     * Indicate that this field has a big number.
     *
     * @return self
     */
    public function big()
    {
        $this->needsToBeUnlocked();

        $this->addOption(Option::bigNumber());

        return $this;
    }

    /**
     * Indicate that this field has a small number.
     *
     * @return self
     */
    public function small()
    {
        $this->needsToBeUnlocked();

        $this->addOption(Option::smallNumber());

        return $this;
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
