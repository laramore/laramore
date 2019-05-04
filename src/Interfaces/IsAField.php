<?php
/**
 * Field interface.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Laramore\Builder;

interface IsAField
{
    /**
     * Define the owner of the field.
     *
     * @param  mixed  $owner
     * @param  string $name  Name of the field.
     * @return static
     */
    public function own($owner, string $name);

    /**
     * Return the current owner.
     *
     * @return mixed
     */
    public function getOwner();

    /**
     * Indicate if the resource is owned.
     *
     * @return boolean
     */
    public function isOwned(): bool;

    /**
     * Lock the resource so no changes could be made.
     *
     * @return static
     */
    public function lock();

    /**
     * Indicate if the resource is locked.
     *
     * @return boolean
     */
    public function isLocked(): bool;

    /**
     * Give the default properties of this field.
     *
     * @return array
     */
    public function getDefaultProperties(): array;

    /**
     * Return all field properties
     *
     * @return array
     */
    public function getProperties(): array;

    /**
     * Indicate if a propery exists.
     *
     * @param  string $key
     * @return boolean
     */
    public function hasProperty(string $key): bool;

    /**
     * Return a property value.
     *
     * @param  string $key
     * @return mixed
     */
    public function getProperty(string $key);

    /**
     * Define a property value.
     *
     * @param string $key
     * @param mixed  $value
     * @return static
     */
    public function setProperty(string $key, $value);

    /**
     * Define the name of the field.
     *
     * @param  string $name
     * @return static
     */
    public function name(string $name);

    /**
     * Handle all calls to define field properies.
     *
     * @param  string $method
     * @param  array  $args
     * @return static
     */
    public function __call(string $method, array $args);

    /**
     * Return a property value.
     *
     * @param  string $key
     * @return mixed
     */
    public function __get(string $key);

    /**
     * Set a property value.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return mixed
     */
    public function __set(string $key, $value);

    /**
     * Indicate if a property exists.
     *
     * @param  string $key
     * @return boolean
     */
    public function __isset(string $key): bool;

    /**
     * Cast the value in the correct format.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function castValue($model, $value);

    /**
     * Give the field value for a specific model.
     *
     * @param  Model $model
     * @param  mixed $value
     * @return mixed
     */
    public function getValue(Model $model, $value);

    /**
     * Return the value to set for a specific model.
     *
     * @param Model $model
     * @param  mixed $value
     * @return mixed
     */
    public function setValue(Model $model, $value);

    /**
     * Return the relation beetween this field and a specific model
     *
     * @param  Model $model
     * @return mixed
     */
    public function relationValue(Model $model);

    /**
     * Add a where condition from this field.
     *
     * @param  Builder $query
     * @param  mixed   ...$args
     * @return Builder|null
     */
    public function whereValue(Builder $query, ...$args);
}
