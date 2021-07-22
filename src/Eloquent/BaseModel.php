<?php
/**
 * Use the Laramore engine with the Eloquent model.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Eloquent;

use Illuminate\Database\Eloquent\Model as Model;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Laramore\Facades\{
    Meta as MetaManager, Operator
};
use Laramore\Contracts\Eloquent\LaramoreModel;
use Laramore\Contracts\Field\{
    ExtraField, IncrementField, NumericField
};
use Laramore\Exceptions\PrepareException;
use Laramore\Fields\Constraint\Primary;
use Laramore\Contracts\Eloquent\LaramoreMeta;
use Laramore\Contracts\Field\RelationField;
use Laramore\Eloquent\Builder;
use Laramore\Eloquent\Meta;
use Laramore\Eloquent\ModelCollection;
use Laramore\Traits\Eloquent\{
    HasLaramoreAttributes, HasLaramoreRelations
};

abstract class BaseModel extends Model implements LaramoreModel
{
    use Macroable, HasLaramoreAttributes, HasLaramoreRelations {
        Macroable::__call as public __callMacro;
        Macroable::__callStatic as public __callStaticMacro;
    }

     /**
     * The name of the "deleted_at at" column.
     *
     * @var string|null
     */
    const DELETED_AT = 'deleted_at';

    /**
     * List all required fields.
     *
     * @var array
     */
    protected $required = [];

    /**
     * List all selected fields.
     *
     * @var array
     */
    public $select = [];

    /**
     * Indicate if the model is currently fetchingDatabase from the database.
     * Public property as the exists one it is (not a good think tbh).
     *
     * @var bool
     */
    public $fetchingDatabase;

    /**
     * Create a new Eloquent model instance.
     * Prepare the model during the creation of the object.
     * Add by default fillable fields, visible fields and the primary key.
     *
     * @param  array   $attributes
     * @param  boolean $fetchingDatabase Is the model currently fetchingDatabase from the database.
     * @return void
     */
    public function __construct(array $attributes=[], bool $fetchingDatabase=false)
    {
        $this->exists = $this->fetchingDatabase = $fetchingDatabase;

        $meta = static::getMeta();

        // Should be locked by a specific Provider later.
        if (!$meta->isLocked()) {
            throw new \Exception('The meta is not locked and cannot be used correctly');
        }

        // Define here fillable and visible fields.
        $this->fillable = $meta->getFieldNamesWithOption('fillable');
        $this->visible = $meta->getFieldNamesWithOption('visible');
        $this->required = $meta->getFieldNamesWithOption('required');
        $this->with = $meta->getFieldNamesWithOption('with', RelationField::class);
        $this->withCount = $meta->getFieldNamesWithOption('with_count');
        $this->appends = array_values(array_diff($meta->getFieldNamesWithOption('with', ExtraField::class), $this->with));
        $this->select = $meta->getFieldNamesWithOption('select');

        $this->timestamps = $meta->hasTimestamps();

        // Define all model metas.
        if ($primary = $meta->getPrimary()) {
            $this->setIncrementing(!$primary->isComposed() && $primary->getAttribute() instanceof IncrementField);
            $this->setKeyType($primary->getAttribute() && $primary->getAttribute() instanceof NumericField ? 'int' : 'string');
        }

        $this->setTable($meta->getTableName());
        $this->setConnection($meta->getConnectionName());

        if (!$this->fetchingDatabase) {
            static::unguarded(function () {
                $this->presetAttributes();
            });
        }

        parent::__construct($attributes);
    }

    /**
     * Get the primary key for the model.
     *
     * @return Primary
     */
    public function getPrimaryKey()
    {
        return static::getMeta()->getPrimary();
    }

    /**
     * Get the primary key for the model.
     *
     * @return string|array
     */
    public function getKeyName()
    {
        $primaryKey = $this->getPrimaryKey();

        if ($primaryKey->isComposed()) {
            return \array_map(function ($attribute) {
                return $attribute->getNative();
            }, $primaryKey->getAttributes());
        }

        return $primaryKey->getAttribute()->getNative();
    }

    /**
     * Set the primary key from the targeted keys for the model.
     *
     * @param  string|array $keys
     * @return $this
     */
    public function setKeyName($keys)
    {
        $keys = \is_array($keys) ? $keys : [$keys];

        $this->setPrimaryKey(static::getMeta()->getConstraintHandler()->getTarget($keys));

        return $this;
    }

    /**
     * Get the value of the model's primary key.
     *
     * @return string|array Depending if the key is composed or not.
     */
    public function getKey()
    {
        if ($this->getPrimaryKey()->isComposed()) {
            $values = [];

            foreach ($this->getKeyName() as $name) {
                $values[$name] = $this->getAttribute($name);
            }

            return $values;
        }

        return $this->getAttribute($this->getKeyName());
    }

    /**
     * Determine if two models have the same ID and belong to the same table.
     *
     * @param  \Illuminate\Database\Eloquent\Model|null  $model
     * @return bool
     */
    public function is($model)
    {
        return ! is_null($model) &&
               $this->getKey() == $model->getKey() &&
               $this->getTable() === $model->getTable() &&
               $this->getConnectionName() === $model->getConnectionName();
    }

    /**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|mixed $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery($query)
    {
        foreach ($this->getPrimaryKey()->getAttributes() as $attribute) {
            $name = $attribute->getName();

            $query->where($name, Operator::equal(), $this->getAttribute($name));
        }

        return $query;
    }

    /**
     * Get the casts array.
     *
     * @return array
     */
    public function getCasts()
    {
        if ($this->getIncrementing()) {
            return array_merge(\array_fill_keys((array) $this->getKeyName(), $this->getKeyType()), $this->casts);
        }

        return $this->casts;
    }

    /**
     * Allow the user to define all meta data for the current model.
     *
     * @param  LaramoreMeta $meta
     * @return mixed
     */
    abstract public static function meta(LaramoreMeta $meta);

    /**
     * Return the meta class to use.
     *
     * @return string
     */
    public static function getMetaClass(): string
    {
        return Meta::class;
    }

    /**
     * Generate one time the model meta.
     *
     * @param  LaramoreMeta $meta
     * @return void
     */
    public static function prepareMeta(LaramoreMeta $meta)
    {
        $meta = static::getMeta();

        if ($meta->isPreparing() || $meta->isPrepared()) {
            throw new PrepareException("Can only prepare unprepared metas. Happened on `{$meta->getModelClass()}", 'prepare');
        }

        $meta->setPreparing();
    }

    /**
     * Get the model meta.
     *
     * @return Meta
     */
    public static function getMeta()
    {
        return MetaManager::get(static::class);
    }

    /**
     * Create a new instance of the given model.
     *
     * @param  array|mixed   $attributes
     * @param  boolean|mixed $fetchingDatabase
     * @return static
     */
    public function newInstance($attributes=[], $fetchingDatabase=false)
    {
        // This method just provides a convenient way for us to generate fresh model
        // instances of this current model. It is particularly useful during the
        // hydration of new objects via the Eloquent query builder instances.
        $model = new static((array) $attributes, $fetchingDatabase);

        $model->setConnection(
            $this->getConnectionName()
        );

        $model->setTable($this->getTable());

        return $model;
    }

    /**
     * Create a new model instance for a related model.
     *
     * @param  string|mixed $class
     * @return mixed
     */
    protected function newRelatedInstance($class)
    {
        $model = tap(new $class([], true), function ($instance) {
            if (!$instance->getConnectionName()) {
                $instance->setConnection($this->connection);
            }
        });
        $model->fetchingDatabase = true;

        return $model;
    }

    /**
     * Begin querying the model.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function query()
    {
        return (new static([], true))->newQuery();
    }

    /**
     * Get a new query builder that doesn't have any global scopes or eager loading.
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newModelQuery(string $relation=null)
    {
        if (! is_null($relation)) {
            return $this->newRelationQuery($relation);
        }

        return parent::newModelQuery();
    }

    /**
     * Get a new query builder that doesn't have any global scopes or eager loading.
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newRelationQuery(string $relation)
    {
        $field = static::getMeta()->getField($relation, RelationField::class);

        return $field->getOwner()->relateFieldValue($field, $this);
    }

    /**
     * Create a new Eloquent query builder for the model.
     * Override the original method.
     *
     * @param  mixed $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    /**
     * Create a new Eloquent Collection instance.
     *
     * @param  array $models
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function newCollection(array $models=[])
    {
        return new ModelCollection($models);
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string|mixed $key
     * @return mixed
     */
    public function __get($key)
    {
        if ($key === 'meta') {
            return static::getMeta();
        }

        return parent::__get(Str::snake($key));
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string|mixed $key
     * @param  mixed        $value
     * @return mixed
     */
    public function __set($key, $value)
    {
        return parent::__set(Str::snake($key), $value);
    }

    /**
     * Determine if an attribute or relation exists on the model.
     *
     * @param  string|mixed $key
     * @return boolean
     */
    public function __isset($key)
    {
        return parent::__isset(Str::snake($key));
    }

    /**
     * Unset an attribute on the model.
     *
     * @param  string|mixed $key
     * @return void
     */
    public function __unset($key)
    {
        parent::__unset(Str::snake($key));
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string|mixed $method
     * @param  array|mixed  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (static::getMeta()->hasField(Str::snake($method), RelationField::class)) {
            return $this->newRelationQuery(Str::snake($method));
        }

        if (static::hasMacro($method)) {
            return static::__callMacro($method, $parameters);
        }

        return parent::__call($method, $parameters);
    }

    /**
     * Handle dynamic static method calls into the model.
     *
     * @param  string|mixed $method
     * @param  array|mixed  $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return static::__callStaticMacro($method, $parameters);
        }

        return parent::__callStatic($method, $parameters);
    }
}
