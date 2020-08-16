<?php
/**
 * Add increment methods.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Traits\Field;

use Laramore\Contracts\Eloquent\LaramoreModel;

trait Increments
{
    /**
     * IncrementField the attribute value by the desired number (1 by default).
     *
     * @param LaramoreModel $model
     * @param integer|float $value
     * @param integer|float $increment
     * @return mixed
     */
    public function increment(LaramoreModel $model, $value, $increment=null)
    {
        return $model->setAttribute($this->getName(), ($value + $increment ?? $this->getConfig('step')));
    }

    /**
     * Decrement the attribute value by the desired number (1 by default).
     *
     * @param LaramoreModel $model
     * @param integer|float $value
     * @param integer|float $decrement
     * @return mixed
     */
    public function decrement(LaramoreModel $model, $value, $decrement=null)
    {
        return $this->increment($model, $value, (-$decrement ?? $this->getConfig('step')));
    }
}
