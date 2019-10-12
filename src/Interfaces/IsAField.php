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

use Illuminate\Support\Collection;
use Laramore\Eloquent\{
    Model, Builder
};
use Laramore\Interfaces\IsProxied;
use Laramore\Elements\Operator;

interface IsAField
{
    /**
     * Define the owner of the field.
     *
     * @param  object $owner
     * @param  string $name  Name of the field.
     * @return static
     */
    public function own(object $owner, string $name);

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
     * @param  mixed  $attvalue
     * @return mixed
     */
    public function __set(string $key, $attvalue);

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
     * @param  mixed $attvalue
     * @return mixed
     */
    public function dry($value);

    /**
     * Cast the value in the correct format.
     *
     * @param  mixed $attvalue
     * @return mixed
     */
    public function cast($value);

    /**
     * Cast the value in the correct format.
     *
     * @param  mixed $attvalue
     * @return mixed
     */
    public function transform($value);

    /**
     * Serialize the value in the correct format.
     *
     * @param  mixed $attvalue
     * @return mixed
     */
    public function serialize($value);

    /**
     * Cast the value in the correct format.
     *
     * @param  mixed $attvalue
     * @return mixed
     */
    public function check($value);

    /**
     * Add a where condition from this field.
     *
     * @param  Builder $builder
     * @param  mixed   ...$args
     * @return Builder|null
     */
    public function whereNull(Builder $builder, $value=null, $boolean='and', $not=false);

    /**
     * Add a where condition from this field.
     *
     * @param  Builder $builder
     * @param  mixed   ...$args
     * @return Builder|null
     */
    public function whereNotNull(Builder $builder, $value=null, $boolean='and');

    /**
     * Add a where condition from this field.
     *
     * @param  Builder $builder
     * @param  mixed   ...$args
     * @return Builder|null
     */
    public function whereIn(Builder $builder, Collection $value=null, $boolean='and', $notIn=false);

    /**
     * Add a where condition from this field.
     *
     * @param  Builder $builder
     * @param  mixed   ...$args
     * @return Builder|null
     */
    public function whereNotIn(Builder $builder, Collection $value=null, $boolean='and');

    /**
     * Add a where condition from this field.
     *
     * @param  Builder $builder
     * @param  mixed   ...$args
     * @return Builder|null
     */
    public function where(Builder $builder, Operator $operator, $value=null, $boolean='and');
}
