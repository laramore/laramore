<?php
/**
 * Define an incrementing field contract.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Contracts\Field;

use Laramore\Contracts\Eloquent\LaramoreModel;

interface IncrementField extends Field
{
    /**
     * Increment the attribute value by the desired number (1 by default).
     *
     * @param LaramoreModel $model
     * @param integer|float $value
     * @param integer|float $increment
     *
     * @return void
     */
    public function increment(LaramoreModel $model, $value, $increment=1);

    /**
     * Decrement the attribute value by the desired number (1 by default).
     *
     * @param LaramoreModel $model
     * @param integer|float $value
     * @param integer|float $decrement
     *
     * @return void
     */
    public function decrement(LaramoreModel $model, $value, $decrement=1);
}
