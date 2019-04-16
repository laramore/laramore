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
     * Call the constructor and generate the field.
     *
     * @param  mixed ...$args
     * @return static
     */
    public static function field(...$args);

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
     * Define if the field must be defined before the model creation.
     *
     * @param  boolean $required
     * @return static
     */
    public function required(bool $required=true);

    /**
     * Indicate that the field is fillable, assignable.
     *
     * @param  boolean $fillable
     * @return static
     */
    public function fillable(bool $fillable=true);

    /**
     * Indicate that the field is visible when exporting.
     *
     * @param  boolean $visible
     * @return static
     */
    public function visible(bool $visible=true);

    /**
     * Indicate that the field is not visible, hidden when exporting.
     *
     * @param  boolean $hidden
     * @return static
     */
    public function hidden(bool $hidden=true);

    /**
     * Indicate that the field is nullable.
     *
     * @param  boolean $nullable
     * @return static
     */
    public function nullable(bool $nullable=true);

    /**
     * Give the default value for the field.
     * If set to null, the default value is null and this defines the nullability of the field.
     *
     * @param  mixed $value
     * @return static
     */
    public function default($value=null);

    /**
     * Cast the value in the correct format.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function castValue($value);

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
