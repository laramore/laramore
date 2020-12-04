<?php
/**
 * Define a numeric field contract.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Contracts\Field;


interface NumericField extends Field
{
    /**
     * Indicate that this field has a big number.
     *
     * @return self
     */
    public function big();

    /**
     * Indicate that this field has a small number.
     *
     * @return self
     */
    public function small();

    /**
     * Force the value to be unsigned or not, positive or not.
     *
     * @return self
     */
    public function unsigned();
}
