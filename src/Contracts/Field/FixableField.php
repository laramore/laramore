<?php
/**
 * Define a pattern field contract.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Contracts\Field;

interface FixableField extends Field
{
    /**
     * Indicate if the value needs to be fixed.
     *
     * @param mixed $value
     * @return boolean
     */
    public function isFixable($value): bool;

    /**
     * Fix the wrong value.
     *
     * @param mixed $value
     * @return mixed
     */
    public function fix($value);
}
