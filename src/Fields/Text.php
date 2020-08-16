<?php
/**
 * Define a text field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

class Text extends BaseAttribute
{
    /**
     * Dry the value in a simple format.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function dry($value)
    {
        return \is_null($value) ? $value : (string) $value;
    }

    /**
     * Hydrate the value in the correct format.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function hydrate($value)
    {
        return \is_null($value) ? $value : (string) $value;
    }

    /**
     * Cast the value in the correct format.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function cast($value)
    {
        return \is_null($value) ? $value : (string) $value;
    }

    /**
     * Serialize the value for outputs.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function serialize($value)
    {
        return $value;
    }
}
