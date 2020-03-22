<?php
/**
 * Inject in models auto fields and relations management.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Traits\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laramore\Facades\{
    Meta as MetaManager,
    Operator
};
use Laramore\Contracts\Field\IncrementField;
use Laramore\Exceptions\MetaException;
use Laramore\Fields\Constraint\Primary;
use Laramore\Meta;

trait HasLaramore
{
    use HasLaramoreAttributes;

    protected $required = [];

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array $attributes
     * @return void
     */
    public function __construct(array $attributes=[])
    {
        if (\version_compare(app()::VERSION, '5.7.0', '<')) {
            $this->bootIfNotBooted();

            $this->initializeHasLaramore();
        }

        parent::__construct($attributes);
    }

    /**
     * Prepare the model during the creation of the object.
     * Add by default fillable fields, visible fields and the primary key.
     */
    protected function initializeHasLaramore()
    {
        $meta = static::getMeta();

        // Should be locked by a specific Provider later.
        if (!$meta->isLocked()) {
            throw new \Exception('The meta is not locked and cannot be used correctly');
        }

        // Define here fillable and visible fields.
        $this->fillable = $meta->getFieldNamesWithOption('fillable');
        $this->visible = $meta->getFieldNamesWithOption('visible');
        $this->required = $meta->getFieldNamesWithOption('required');
        $this->appends = $meta->getFieldNamesWithOption('appends');
        $this->with = $meta->getFieldNamesWithOption('with');
        $this->withCount = $meta->getFieldNamesWithOption('with_count');
        $this->timestamps = $meta->hasTimestamps();

        // Define all model metas.
        if ($primary = $meta->getPrimary()) {
            $this->setPrimaryKey($primary);

            $this->setIncrementing(!$primary->isComposed() && $primary->getAttribute() instanceof IncrementField);
        }

        $this->setTable($meta->getTableName());
        $this->setConnection($meta->getConnectionName());
        $this->resetAttributes();
    }

    /**
     * Get the primary key for the model.
     *
     * @return Primary
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * Set the primary key for the model.
     *
     * @param  Primary $key
     * @return $this
     */
    public function setPrimaryKey(Primary $key)
    {
        $this->primaryKey = $key;

        return $this;
    }

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getKeyName()
    {
        return $this->getPrimaryKey()->getAttnames();
    }

    /**
     * Set the primary key for the model.
     *
     * @param  string $key
     * @return $this
     */
    public function setKeyName($key)
    {
        $this->setPrimaryKey(static::getMeta()->getConstraintHandler()->getTarget($key));

        return $this;
    }

    /**
     * Get the value of the model's primary key.
     *
     * @return mixed
     */
    public function getKey()
    {
        return $this->getPrimaryKey()->getModelValues($this);
    }

    /**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery(Builder $query)
    {
        foreach ($this->getPrimaryKey()->getAttnames() as $attname) {
            $query->where($attname, Operator::equal(), $this->getAttribute($attname));
        }

        return $query;
    }

    /**
     * Execute a query for a single record by ID.
     *
     * @param  array $ids     Array of keys, like [column => value].
     * @param  array $columns
     * @return mixed|static
     */
    public static function find($ids, $columns=['*'])
    {
        $instance = new static;
        $query = $instance->newQuery();
        $primary = $instance->getPrimaryKey();
        $ids = \is_array($ids) ? $ids : [$ids];

        if ($primary->isComposed()) {
            foreach ($primary->getAttnames() as $index => $attname) {
                $query->where($attname, Operator::equal(), Arr::isAssoc($ids) ? $ids[$attname] : $ids[$index]);
            }
        } else {
            $attname = $primary->getAttname();

            $query->where($attname, Operator::equal(), Arr::isAssoc($ids) ? $ids[$attname] : $ids[0]);
        }

        return $query->first($columns);
    }

    /**
     * Get the casts array.
     *
     * @return array
     */
    public function getCasts()
    {
        if ($this->getIncrementing()) {
            return array_merge(\array_fill_keys($this->getKeyName(), $this->getKeyType()), $this->casts);
        }

        return $this->casts;
    }

    /**
     * Allow the user to define all meta data for the current model.
     *
     * @param  Meta $meta
     * @return void
     */
    abstract protected static function __meta(Meta $meta);

    /**
     * Generate one time the model meta.
     *
     * @return void
     */
    public static function generateMeta()
    {
        if (MetaManager::has(static::class)) {
            throw new MetaException(MetaManager::get(static::class), 'The meta already exists for `'.static::class.'`');
        }

        // Generate all meta data defined by the user in the current pivot.
        $class = static::getMetaClass();
        MetaManager::add($meta = new $class(static::class));

        static::__meta($meta);

        return $meta;
    }

    /**
     * Return the meta class to use.
     *
     * @return string
     */
    public static function getMetaClass(): string
    {
        return config('meta.class');
    }

    /**
     * Get the model meta.
     *
     * @return Meta
     */
    public static function getMeta()
    {
        if (!MetaManager::has(static::class)) {
            return static::generateMeta();
        }

        return MetaManager::get(static::class);
    }

    /**
     * Create a new instance of the given model.
     *
     * @param  array   $attributes
     * @param  boolean $exists
     * @return static
     */
    public function newInstance($attributes=[], $exists=false)
    {
        // This method just provides a convenient way for us to generate fresh model
        // instances of this current model. It is particularly useful during the
        // hydration of new objects via the Eloquent query builder instances.
        $model = new static((array) $attributes);

        $model->exists = $exists;

        $model->setConnection(
            $this->getConnectionName()
        );

        $model->setTable($this->getTable());

        return $model;
    }

    /**
     * Create a new model instance that is existing.
     *
     * @param  array       $attributes
     * @param  string|null $connection
     * @return static
     */
    public function newFromBuilder($attributes=[], $connection=null)
    {
        $model = $this->newInstance([], true);

        $model->setRawAttributes((array) $attributes, true);

        $model->setConnection($connection ?: $this->getConnectionName());

        $model->fireModelEvent('retrieved', false);

        return $model;
    }

    public static function getEloquentBuilderClass()
    {
        return config('meta.builder_class');
    }

    /**
     * Create a new Eloquent query builder for the model.
     * Override the original method.
     *
     * @param  mixed $query
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        $class = static::getEloquentBuilderClass();

        return new $class($query);
    }

    /**
     * Unset the value for a given offset.
     *
     * @param  mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->resetAttribute($offset);
    }

    /**
     * Call a proxy by its name.
     *
     * @param mixed $name
     * @param mixed $args
     * @return mixed
     */
    public function __proxy($name, $args)
    {
        $proxy = static::getMeta()->getProxyHandler()->get($name);

        if ($proxy->isStatic()) {
            throw new \BadMethodCallException("The proxy `{$proxy->getName()}` must be called statically.");
        }

        return $proxy->__invoke($this, ...$args);
    }

    /**
     * Return a static proxy by its name.
     *
     * @param mixed $name
     * @param mixed $args
     * @return mixed
     */
    public static function __proxyStatic($name, $args)
    {
        $proxy = static::getMeta()->getProxyHandler()->get($name);

        if (!$proxy->isStatic()) {
            throw new \BadMethodCallException("The proxy `{$proxy->getName()}` cannot be called statically.");
        }

        return $proxy->__invoke(...$args);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string $methodName
     * @param  mixed  $args
     * @return void
     */
    public function __call($methodName, $args)
    {
        $methodName = Str::camel($methodName);

        if (static::getMeta()->getProxyHandler()->has($methodName)) {
            return $this->__proxy($methodName, $args);
        }

        return parent::__call($methodName, $args);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string $methodName
     * @param  mixed  $args
     * @return void
     */
    public static function __callStatic($methodName, $args)
    {
        $methodName = Str::camel($methodName);

        if (static::getMeta()->getProxyHandler()->has($methodName)) {
            return static::__proxyStatic($methodName, $args);
        }

        return parent::__callStatic($methodName, $args);
    }
}
