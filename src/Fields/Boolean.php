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

class Boolean extends BaseAttribute
{
    /**
     * Dry the value in a simple format.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function dry($value)
    {
        return \is_null($value) ? $value : (bool) $value;
    }

    /**
     * Hydrate the value in a simple format.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function hydrate($value)
    {
        return \is_null($value) ? $value : (bool) $value;
    }

    /**
     * Cast the value in the correct format.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function cast($value)
    {
        return \is_null($value) ? $value : (bool) $value;
    }

    /**
     * Serialize the value for outputs.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function serialize($value)
    {
        return \is_null($value) ? $value : (bool) $value;
    }

    /**
     * Return if the value is true or false as expected.
     *
     * @param  boolean|null $value
     * @param  boolean      $expected
     * @return boolean
     */
    public function is(bool $value=null, bool $expected=true): bool
    {
        return $value === $expected;
    }

    /**
     * Return if the value is false.
     *
     * @param  boolean|null $value
     * @return boolean
     */
    public function isNot(bool $value): bool
    {
        return $this->is($value, false);
    }
}
