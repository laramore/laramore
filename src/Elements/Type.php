<?php
/**
 * Define a specific field type element.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Elements;

class Type extends BaseElement
{
    /**
     * Tell if a value is of the right type.
     *
     * @param  mixed $value
     * @return boolean
     */
    public function isType($value): bool
    {
        $native = $this->getValue('native');

        if ($native instanceof \Closure) {
            return $native($value);
        }

        $type = "\is_$native";

        return $type($value);
    }
}
