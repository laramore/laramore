<?php
/**
 * Add management for extra fields.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Traits\Field;

use Laramore\Contracts\Eloquent\LaramoreModel;

trait ModelExtra
{

    /**
     * Set the value for the field.
     *
     * @param LaramoreModel|array|\ArrayAccess $model
     * @param  mixed                            $value
     * @return mixed
     */
    public function set($model, $value)
    {
        if ($model instanceof LaramoreModel) {
            return $model->setExtraValue($this->getName(), $value);
        }

        if (\is_array($model) || ($model instanceof \ArrayAccess)) {
            if (\is_object($model) || Arr::isAssoc($model)) {
                return $model[$this->getName()] = $value;
            }
        }
    }
}
