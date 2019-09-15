<?php
/**
 * Define a boolean field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

use Laramore\Facades\TypeManager;
use Illuminate\Database\Eloquent\Model;
use Laramore\Type;

class Boolean extends Field
{
    /**
     * Return the type object of the field.
     *
     * @return Type
     */
    public function getType(): Type
    {
        return TypeManager::boolean();
    }

    protected function setProxies()
    {
        parent::setProxies();

        $this->setProxy('is', ['value']);
    }

    public function dry($value)
    {
        return is_null($value) ? $value : (boolean) $value;
    }

    public function cast($value)
    {
        return is_null($value) ? $value : (boolean) $value;
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

    /**
     * Return if the value is true or false as expected.
     *
     * @param  mixed   $value
     * @param  boolean $expected
     * @return boolean
     */
    public function is(?bool $value, bool $expected=true): bool
    {
        return $value === $expected;
    }
}
