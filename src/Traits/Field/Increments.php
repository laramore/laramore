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
     * Incremental step.
     *
     * @var integer|float
     */
    protected $step;

    /**
     * IncrementField the attribute value by the desired number (1 by default).
     *
     * @param LaramoreModel|array|\Illuminate\Contracts\Support\\ArrayAccess $model
     * @param integer|float $value
     * @param integer|float $increment
     * @return mixed
     */
    public function increment($model, $value, $increment=null)
    {
        return $model->setAttribute($this->getName(), ($value + $increment ?? $this->step));
    }

    /**
     * Decrement the attribute value by the desired number (1 by default).
     *
     * @param LaramoreModel|array|\Illuminate\Contracts\Support\\ArrayAccess $model
     * @param integer|float $value
     * @param integer|float $decrement
     * @return mixed
     */
    public function decrement($model, $value, $decrement=null)
    {
        return $this->increment($model, $value, (-$decrement ?? $this->step));
    }
}
