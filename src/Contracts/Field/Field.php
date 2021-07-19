<?php
/**
 * Field contract.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Contracts\Field;

use Illuminate\Support\Collection;
use Laramore\Contracts\{
    Locked, Owned, Eloquent\LaramoreModel, Eloquent\LaramoreBuilder
};
use Laramore\Elements\OperatorElement;
use Laramore\Fields\Constraint\FieldConstraintHandler;

interface Field extends Locked, Owned
{
    /**
     * Return the native value of this field.
     * Commonly, its name.
     *
     * @return string
     */
    public function getNative(): string;

    /**
     * Return the fully qualified name.
     *
     * @return string
     */
    public function getQualifiedName(): string;

    /**
     * Define all options for this field.
     *
     * @param array $options
     * @return self
     */
    public function options(array $options);

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
     * @return self
     */
    public function setProperty(string $key, $value);

    /**
     * Return the relation handler for this meta.
     *
     * @return FieldConstraintHandler
     */
    public function getConstraintHandler();

    /**
     * Handle all calls to define field properies.
     *
     * @param  string $method
     * @param  array  $args
     * @return self
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
     * Cast user value into field format.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function cast($value);

    /**
     * Serialize the value for output.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function serialize($value);

    /**
     * Indicate if the field has a value.
     *
     * @param  LaramoreModel|array|\Illuminate\Contracts\Support\\ArrayAccess $model
     * @return mixed
     */
    public function has($model);

    /**
     * Get the value definied by the field.
     *
     * @param  LaramoreModel|array|\Illuminate\Contracts\Support\\ArrayAccess $model
     * @return mixed
     */
    public function get($model);

    /**
    * Retrieve values from the database.
    *
    * @param LaramoreModel|array|\ArrayAccess $model
    * @return mixed
    */
   public function retrieve($model);

    /**
     * Set the value for the field.
     *
     * @param  LaramoreModel|array|\Illuminate\Contracts\Support\\ArrayAccess $model
     * @param  mixed                                                          $value
     * @return mixed
     */
    public function set($model, $value);

    /**
     * Reset the value for the field.
     *
     * @param  LaramoreModel|array|\Illuminate\Contracts\Support\\ArrayAccess $model
     * @return mixed
     */
    public function reset($model);

    /**
     * Add a where null condition from this field.
     *
     * @param  LaramoreBuilder $builder
     * @param  string          $boolean
     * @param  boolean         $not
     * @return LaramoreBuilder
     */
    public function whereNull(LaramoreBuilder $builder, string $boolean='and', bool $not=false): LaramoreBuilder;

    /**
     * Add a where not null condition from this field.
     *
     * @param  LaramoreBuilder $builder
     * @param  string          $boolean
     * @return LaramoreBuilder
     */
    public function whereNotNull(LaramoreBuilder $builder, string $boolean='and'): LaramoreBuilder;

    /**
     * Add a where in condition from this field.
     *
     * @param  LaramoreBuilder $builder
     * @param  Collection      $value
     * @param  string          $boolean
     * @param  boolean         $notIn
     * @return LaramoreBuilder
     */
    public function whereIn(LaramoreBuilder $builder, Collection $value=null,
                            string $boolean='and', bool $notIn=false): LaramoreBuilder;

    /**
     * Add a where not in condition from this field.
     *
     * @param  LaramoreBuilder $builder
     * @param  Collection      $value
     * @param  string          $boolean
     * @return LaramoreBuilder
     */
    public function whereNotIn(LaramoreBuilder $builder,
                               Collection $value=null, string $boolean='and'): LaramoreBuilder;

    /**
     * Add a where condition from this field.
     *
     * @param  LaramoreBuilder $builder
     * @param  OperatorElement $operator
     * @param  mixed           $value
     * @param  string          $boolean
     * @return LaramoreBuilder
     */
    public function where(LaramoreBuilder $builder, OperatorElement $operator,
                          $value=null, string $boolean='and'): LaramoreBuilder;
}
