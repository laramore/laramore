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

use Illuminate\Support\Arr;
use Laramore\Contracts\Eloquent\LaramoreModel;

trait ModelExtra
{
    /**
     * Indicate if the field has a value.
     *
     * @param LaramoreModel|array|\ArrayAccess $model
     * @return mixed
     */
    public function has($model)
    {
        if ($model instanceof LaramoreModel) {
            return $model->hasExtraValue($this->getName());
        }

        if (\is_array($model) || ($model instanceof \ArrayAccess)) {
            if (\is_object($model) || Arr::isAssoc($model)) {
                return isset($model[$this->getName()]);
            }
        }

        return false;
    }

    /**
     * Get the value definied by the field.
     *
     * @param LaramoreModel|array|\ArrayAccess $model
     * @return mixed
     */
    public function get($model)
    {
        if ($model instanceof LaramoreModel) {
            return $model->getExtraValue($this->getName());
        }

        if (\is_array($model) || ($model instanceof \ArrayAccess)) {
            if (\is_object($model) || Arr::isAssoc($model)) {
                return $model[$this->getName()];
            } else if (isset($model[0])) {
                return $model[0];
            }
        }
    }

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

    /**
     * Reet the value for the field.
     *
     * @param LaramoreModel|array|\ArrayAccess $model
     * @return mixed
     */
    public function reset($model)
    {
        if ($this->hasDefault()) {
            return $this->set($model, $this->getDefault());
        }

        if ($model instanceof LaramoreModel) {
            return $model->unsetExtra($this->getName());
        }

        if (\is_array($model) || ($model instanceof \ArrayAccess)) {
            if (\is_object($model) || Arr::isAssoc($model)) {
                unset($model[$this->getName()]);
            }
        }
    }
}
