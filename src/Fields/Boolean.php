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

use Illuminate\Database\Eloquent\Model;
use Laramore\Elements\Type as ReturnedType;
use Type;

class Boolean extends Field
{
    /**
     * Return the type object of the field.
     *
     * @return Type
     */
    public function getType(): ReturnedType
    {
        return Type::boolean();
    }

    protected function setProxies()
    {
        parent::setProxies();

        $this->setProxy('is', ['value']);
        $this->setProxy('isNot', ['value']);
    }

    public function dry($value)
    {
        return $this->transform($value);
    }

    public function cast($value)
    {
        return $this->transform($value);
    }

    public function transform($value)
    {
        if (is_null($value)) {
            return $value;
        }

        return (boolean) $value;
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

    /**
     * Return if the value is false.
     *
     * @param  mixed   $value
     * @return boolean
     */
    public function isNot(?bool $value): bool
    {
        return $this->is($value, false);
    }
}
