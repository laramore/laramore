<?php
/**
 * Laramore pivot.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Contracts\Eloquent;

use Illuminate\Database\Eloquent\Model;

interface LaramorePivot extends LaramoreModel
{
    /**
     * Create a new pivot model instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $parent
     * @param  array  $attributes
     * @param  string  $table
     * @param  bool  $exists
     * @return static
     */
    public static function fromAttributes(Model $parent, $attributes, $table, $exists = false);

    /**
     * Create a new pivot model from raw values returned from a query.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $parent
     * @param  array  $attributes
     * @param  string  $table
     * @param  bool  $exists
     * @return static
     */
    public static function fromRawAttributes(Model $parent, $attributes, $table, $exists = false);

    /**
     * Get the foreign key column name.
     *
     * @return string
     */
    public function getForeignKey();

    /**
     * Get the "related key" column name.
     *
     * @return string
     */
    public function getRelatedKey();

    /**
     * Get the "related key" column name.
     *
     * @return string
     */
    public function getOtherKey();

    /**
     * Set the key names for the pivot model instance.
     *
     * @param  string  $foreignKey
     * @param  string  $relatedKey
     * @return $this
     */
    public function setPivotKeys($foreignKey, $relatedKey);

    /**
     * Determine if the pivot model or given attributes has timestamp attributes.
     *
     * @param  array|null  $attributes
     * @return bool
     */
    public function hasTimestampAttributes($attributes = null);

    /**
     * Get the name of the "created at" column.
     *
     * @return string
     */
    public function getCreatedAtColumn();

    /**
     * Get the name of the "updated at" column.
     *
     * @return string
     */
    public function getUpdatedAtColumn();

    /**
     * Get the queueable identity for the entity.
     *
     * @return mixed
     */
    public function getQueueableId();

    /**
     * Get a new query to restore one or more models by their queueable IDs.
     *
     * @param  int[]|string[]|string  $ids
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newQueryForRestoration($ids);

    /**
     * Unset all the loaded relations for the instance.
     *
     * @return $this
     */
    public function unsetRelations();
}
