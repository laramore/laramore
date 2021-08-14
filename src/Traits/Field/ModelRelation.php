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
