<?php
/**
 * Relation field contract.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Contracts\Field;

use Laramore\Contracts\{
    Eloquent\LaramoreModel, Field\Constraint\Constraint
};

interface RelationField extends Field
{
    /**
     * Return the relation with this field.
     *
     * @param  LaramoreModel $model
     * @return mixed
     */
    public function relate(LaramoreModel $model);

    /**
     * Add a condition to the relation.
     *
     * @param  callable|\Closure $callable
     * @return self
     */
    public function when($callable);

    /**
     * Reverbate the relation into database or other fields.
     * It should be called by the set method.
     *
     * @param  LaramoreModel $model
     * @param  mixed         $value
     * @return mixed
     */
    public function reverbate(LaramoreModel $model, $value);

    /**
     * Return the reversed field.
     *
     * @return RelationField
     */
    public function getReversedField(): RelationField;

    /**
     * Indicate if the relation is head on or not.
     * Usefull to know which to use between source and target.
     *
     * @return boolean
     */
    public function isRelationHeadOn(): bool;

    /**
     * Model where the relation is set from.
     *
     * @return string
     */
    public function getSourceModel(): string;

    /**
     * Model where the relation is set to.
     *
     * @return string
     */
    public function getTargetModel(): string;

    /**
     * Return the source of the relation.
     *
     * @return Constraint
     */
    public function getSource(): Constraint;

    /**
     * Return the target of the relation.
     *
     * @return Constraint
     */
    public function getTarget(): Constraint;

    /**
     * Update a relation.
     *
     * @param LaramoreModel $model
     * @param array         $value
     * @return boolean
     */
    public function update(LaramoreModel $model, array $value): bool;

    /**
     * Delete relation.
     *
     * @param LaramoreModel $model
     * @return integer
     */
    public function delete(LaramoreModel $model): int;
}
