<?php
/**
 * Add management for relation fields.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Traits\Field;

use Illuminate\Support\Arr;
use Laramore\Contracts\Eloquent\LaramoreModel;

trait ModelRelation
{
    /**
     * Define condition on relation.
     *
     * @var callable|\Closure
     */
    protected $when;

    /**
     * Add a condition to the relation.
     *
     * @param  callable|\Closure $callable
     * @return self
     */
    public function when($callable)
    {
        $this->needsToBeUnLocked();

        $this->when = $callable;

        return $this;
    }

    /**
     * Indicate if the field has a value.
     *
     * @param LaramoreModel|array|\ArrayAccess $model
     * @return mixed
     */
    public function has($model)
    {
        if ($model instanceof LaramoreModel) {
            return $model->hasRelationValue($this->getName());
        }

        if (\is_array($model) || ($model instanceof \ArrayAccess)) {
            if (\is_object($model) || Arr::isAssoc($model)) {
                return isset($model[$this->getName()]);
            } else if (isset($model[0])) {
                return $model[0];
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
            return $model->getRelationValue($this->getName());
        }

        if (\is_array($model) || ($model instanceof \ArrayAccess)) {
            if (\is_object($model) || Arr::isAssoc($model)) {
                return $model[$this->getName()];
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
            return $model->setRelationValue($this->getName(), $value);
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
            return $model->unsetRelation($this->getName());
        }

        if (\is_array($model) || ($model instanceof \ArrayAccess)) {
            if (\is_object($model) || Arr::isAssoc($model)) {
                unset($model[$this->getName()]);
            }
        }
    }

    /**
     * Retrieve values from the relation field.
     *
     * @param LaramoreModel|array|\ArrayAccess $model
     * @return mixed
     */
    public function retrieve($model)
    {
        if ($model instanceof LaramoreModel) {
            return $this->relate($model)->getResults();
        }

        return $this->getDefault();
    }

    /**
     * Update a relation.
     *
     * @param LaramoreModel $model
     * @param array         $value
     * @return boolean
     */
    public function update(LaramoreModel $model, array $value): bool
    {
        return $this->relate($model)->update($value);
    }

    /**
     * Delete a relation.
     *
     * @param LaramoreModel $model
     * @return integer
     */
    public function delete(LaramoreModel $model): int
    {
        return $this->relate($model)->delete();
    }
}
