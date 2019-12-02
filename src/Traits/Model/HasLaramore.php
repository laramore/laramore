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

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\{
    Builder, MassAssignmentException
};
use Laramore\Fields\{
    Field, CompositeField, LinkField
};
use Laramore\Eloquent\{
    Builder as LaramoreBuilder, Relations\BaseCollection as RelationCollection
};
use Laramore\Proxies\{
    BaseProxy, MultiProxy, ProxyHandler
};
use Laramore\Meta;
use Types, Metas, Proxies;

trait HasLaramore
{
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
    public function initializeHasLaramore()
    {
        $meta = static::getMeta();

        // Should be locked by a specific Provider later.
        if (!$meta->isLocked()) {
            throw new \Exception('The meta is not locked and cannot be used correctly');
        }

        // Define here fillable and visible fields.
        $this->fillable = $meta->getFillableFieldNames();
        $this->visible = $meta->getVisibleFieldNames();
        $this->required = $meta->getRequiredFieldNames();
        $this->timestamps = $meta->hasTimestamps();

        // Define all model metas.
        if ($primary = $meta->getPrimary()) {
            $this->setKeyName($primary->isComposed() ? $primary->getAttributes() : $primary->getAttribute());

            if (!$primary->isComposed()) {
                $this->setIncrementing($primary->all()[0]->type === Types::increment());
            }
        }

        $this->setTable($meta->getTableName());
        $this->resetAttributes();
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
    protected static function generateMeta()
    {
        // Generate all meta data defined by the user in the current pivot.
        $class = static::getMetaClass();
        Metas::add($meta = new $class(static::class));

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
        return config('metas.class');
    }

    /**
     * Get the model meta.
     *
     * @return Meta
     */
    public static function getMeta()
    {
        if (!Metas::has(static::class)) {
            return static::generateMeta();
        }

        return Metas::get(static::class);
    }

    /**
     * Return if a field name exists or not.
     * The name could be from one field, link field or composite field.
     *
     * @param  string $key
     * @return boolean
     */
    public static function hasField(string $key)
    {
        return static::getMeta()->has($key);
    }

    /**
     * Get a field from its name.
     * The name could be from one field, link field or composite field.
     *
     * @param  string $key
     * @return mixed
     *
     * @throws Exception Except if the field does not exist.
     */
    public static function getField(string $key)
    {
        return static::getMeta()->get($key);
    }

    /**
     * Return all fields: fields, link fields and composite fields.
     *
     * @return array
     */
    public static function getFields()
    {
        return static::getMeta()->all();
    }

    /**
     * Update the creation and update timestamps.
     *
     * @return void
     */
    protected function updateTimestamps()
    {
        $time = $this->freshTimestamp();

        if (!$this->exists && !is_null(static::CREATED_AT) && !$this->isDirty(static::CREATED_AT)) {
            $this->setCreatedAt($time);
        }

        // Only update the updated field if the model already exists or the field cannot be null.
        if (!\is_null(static::UPDATED_AT) && !$this->isDirty(static::UPDATED_AT) && (
            $this->exists || !static::getField(static::UPDATED_AT)->nullable
        )) {
            $this->setUpdatedAt($time);
        }
    }

    /**
     * Dry a value for a specific field.
     *
     * @param  string $key   Name of the field.
     * @param  mixed  $value
     * @return mixed		 The naturalized value.
     */
    public static function dry(string $key, $value)
    {
        return ($field = static::getField($key))->getOwner()->dryFieldAttribute($field, $value);
    }

    /**
     * Cast a value for a specific field.
     *
     * @param  string $key   Name of the field.
     * @param  mixed  $value
     * @return mixed		 The casted value.
     */
    public static function cast(string $key, $value)
    {
        return ($field = static::getField($key))->getOwner()->castFieldAttribute($field, $value);
    }

    /**
     * Re turn the default value for a specific field.
     *
     * @param  string $key Name of the field.
     * @return mixed		 The casted value.
     */
    public static function transform(string $key, $value)
    {
        return ($field = static::getField($key))->getOwner()->transformFieldAttribute($field, $value);
    }

    /**
     * Re turn the default value for a specific field.
     *
     * @param  string $key Name of the field.
     * @return mixed		 The casted value.
     */
    public static function serialize(string $key, $value)
    {
        return ($field = static::getField($key))->getOwner()->serializeFieldAttribute($field, $value);
    }

    /**
     * Re turn the default value for a specific field.
     *
     * @param  string $key Name of the field.
     * @return mixed		 The casted value.
     */
    public static function default(string $key)
    {
        return ($field = static::getField($key))->getOwner()->defaultFieldAttribute($field);
    }

    /**
     * Reset a specific field.
     *
     * @param  string $key Name of the field.
     * @return $this
     */
    public function resetAttribute(string $key)
    {
        ($field = static::getField($key))->getOwner()->resetFieldAttribute($field, $this);

        return $this;
    }

    public function resetAttributes()
    {
        foreach (static::getMeta()->getFields() as $field) {
            $field->getOwner()->resetFieldAttribute($field, $this);
        }

        return $this;
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (! $key) {
            return;
        }

        // If the attribute exists in the attribute array or has a "get" mutator we will
        // get the attribute's value. Otherwise, we will proceed as if the developers
        // are asking for a relationship's value. This covers both types of values.
        if (array_key_exists($key, $this->attributes) ||
            $this->hasGetMutator($key)) {
            return $this->getAttributeValue($key);
        }

        // Here we will determine if the model base class itself contains this given key
        // since we don't want to treat any of those methods as relationships because
        // they are all intended as helper methods and none of these are relations.
        if (method_exists(self::class, $key)) {
            return;
        }

        return $this->getRelationValue($key);
    }

    /**
     * Get a plain attribute (not a relationship).
     * Override the original method.
     *
     * @param  mixed $key
     * @return mixed
     */
    public function getAttributeValue($key)
    {
        // If the attribute has a get mutator, we will call that then return what
        // it returns as the value, which is useful for transforming values on
        // retrieval from the model to a form that is more useful for usage.
        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $this->getAttributeFromArray($key));
        }

        // If the user did not set any custom methods to handle this attribute,
        // we call the field getter.
        if (static::hasField($key)) {
            $field = static::getField($key);

            return $field->getOwner()->getFieldAttribute($field, $this);
        }

        return $this->getRawAttribute($key);
    }

    /**
     * Get an attribute from the $attributes array.
     *
     * @param  string $key
     * @return mixed
     */
    public function getAttributeFromArray($key)
    {
        if (isset($this->attributes[$key])) {
            return $this->getRawAttribute($key);
        }

        throw new \Exception("The model has no field and value for the key $key");
    }

    /**
     * Get the relation value for a specific key.
     *
     * @param  string $key Not specified because Model has no parameter types.
     * @return mixed
     */
    public function getRelation($key)
    {
        return \call_user_func([$this, $key]);
    }

    /**
     * Get a relationship.
     * Override the original method.
     *
     * @param  mixed $key
     * @return mixed
     */
    public function getRelationValue($key)
    {
        // If the key already exists in the relationships array, it just means the
        // relationship has already been locked, so we'll just return it out of
        // here because there is no need to query within the relations twice.
        if ($this->relationLoaded($key)) {
            return $this->getRawRelationValue($key);
        }

        // If the "attribute" exists as a method on the model, we will just assume
        // it is a relationship and will load and return results from the query
        // and hydrate the relationship's value on the "relationships" array.
        if (\method_exists($this, $key)) {
            return $this->getRelationshipFromMethod($key);
        }

        // If the user did not set any custom methods to handle this attribute,
        // we call the field getter.
        if (static::getMeta()->has($key)) {
            $field = static::getMeta()->get($key);

            return tap($field->getOwner()->getRelationFieldAttribute($field, $this), function ($results) use ($key) {
                $this->setRawRelationValue($key, $results);
            });
        }
    }

    public function getRawRelationValue($key)
    {
        return $this->relations[$key];
    }

    /**
     * Get a relationship value from a method.
     *
     * @param  string $method
     * @return mixed
     *
     * @throws \LogicException
     */
    protected function getRelationshipFromMethod($method)
    {
        $relation = $this->$method();

        if (! $relation instanceof Relation) {
            throw new LogicException(sprintf(
                '%s::%s must return a relationship instance.', static::class, $method
            ));
        }

        return tap($relation->getResults(), function ($results) use ($method) {
            $this->setRawRelationValue($method, $results);
        });
    }

    /**
     * Set the given relationship on the model.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return $this
     */
    public function setRelationValue($key, $value)
    {
        $field = static::getMeta()->get($key);

        $field->getOwner()->setRelationFieldAttribute($field, $this, $value);

        return $this;
    }

    /**
     * Set the given relationship on the model.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return $this
     */
    public function setRawRelationValue($key, $value)
    {
        $this->relations[$key] = $value;

        return $this;
    }

    /**
     * Set the array of model attributes. No checking is done.
     *
     * @param  array $attributes
     * @param  mixed $sync
     * @return $this
     */
    public function setAttributes(array $attributes, $sync=false)
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        if ($sync) {
            $this->syncOriginal();
        }

        return $this;
    }

    /**
     * Set a given attribute on the model.
     * Override the original method.
     *
     * @param  mixed $key
     * @param  mixed $value
     * @return mixed
     *
     * @throws Exception Except if the field is not fillable.
     */
    public function setAttribute($key, $value)
    {
        if ($this->hasSetMutator($key)) {
            // First we will check for the presence of a mutator for the set operation
            // which simply lets the developers tweak the attribute as it is set on
            // the model, such as "json_encoding" an listing of data for storage.
            return $this->setMutatedAttributeValue($key, $value);
        }

        // Check if the field exists to cast the value.
        if (static::hasField($key)) {
            $field = static::getField($key);

            // If the field is not fillable, throw an exception.
            if (!$this->isFillable($key)) {
                throw new MassAssignmentException(sprintf(
                    'Add [%s] to fillable property to allow mass assignment on [%s].',
                    $key, get_class($this)
                ));
            }

            $field->getOwner()->setFieldAttribute($field, $this, $value);
        } else {
            $this->attributes[$key] = $value;
        }

        return $this;
    }

    /**
     * Set the array of model attributes. No checking is done.
     *
     * @param  array $attributes
     * @param  mixed $sync
     * @return $this
     */
    public function setRawAttributes(array $attributes, $sync=false)
    {
        foreach ($attributes as $key => $value) {
            $this->setRawAttribute($key, $value);
        }

        if ($sync) {
            $this->syncOriginal();
        }

        return $this;
    }

    public function setRawAttribute(string $key, $value)
    {
        $this->attributes[$key] = static::hasField($key) ? static::cast($key, $value) : $value;

        return $this;
    }

    public function getRawAttribute(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    protected function finishSave(array $options=[])
    {
        if ($options['relate'] ?? false) {
            $this->saveRelations();
        }

        return parent::finishSave($options);
    }

    public function saveRelations(array $relations=null)
    {
        $status = true;
        $meta = static::getMeta();

        if (\is_null($relations)) {
            $relationsToSave = $this->relations;
        } else {
            $relationsToSave = \array_intersect_key($this->relations, \array_flip($relations));
        }

        foreach ($relationsToSave as $key => $relation) {
            $field = $meta->get($key);

            $status = $status && $field->getOwner()->reverbateRelationFieldAttribute($field, $this, $relation);
        }

        return $status;
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

    /**
     * Insert the given attributes and set the ID on the model.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array                                 $attributes
     * @return void
     */
    protected function insertAndSetId(Builder $query, $attributes)
    {
        $id = $query->insertGetId($attributes, $keyName = $this->getKeyName());

        $this->setRawAttribute($keyName, $id);
    }

    public static function getEloquentBuilderClass()
    {
        return LaramoreBuilder::class;
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
     * Convert the model's attributes to an array.
     *
     * @return array
     */
    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        foreach ($this->getArrayableAttributes() as $key => $value) {
            if (static::hasField($key)) {
                $attributes[$key] = static::serialize($key, $value);
            }
        }

        return $attributes;
    }

    /**
     * Convert the model's attributes to an array.
     *
     * @return array
     */
    public function relationsToArray()
    {
        $relations = parent::relationsToArray();

        foreach ($this->getArrayableRelations() as $key => $value) {
            if (static::hasField($key)) {
                $relations[$key] = static::serialize($key, $value);
            }
        }

        return $relations;
    }

    /**
     * Register a model event with the dispatcher.
     *
     * @param  string          $event
     * @param  \Closure|string $callback
     * @return void
     */
    public static function addModelEvent(string $event, $callback)
    {
        static::registerModelEvent($event, $callback);
    }

    public static function getProxyHandler(): ProxyHandler
    {
        return Proxies::getHandler(static::class);
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string $action
     * @return mixed
     */
    public function __get($action)
    {
        $name = Str::snake($action);

        if (static::hasField($name)) {
            return call_user_func([$this, 'get'.Str::studly($action).'Attribute']);
        }

        return parent::__get($action);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string $action
     * @param  mixed  $value
     * @return void
     */
    public function __set($action, $value)
    {
        $name = Str::snake($action);

        if (static::hasField($name)) {
            return call_user_func([$this, 'set'.Str::studly($action).'Attribute'], $value);
        }

        return parent::__set($action);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string $action
     * @param  mixed  $args
     * @return void
     */
    public function __call($action, $args)
    {
        $proxyHandler = static::getProxyHandler();

        if ($proxyHandler->has($action, $proxyHandler::MODEL_TYPE)) {
            return static::getMeta()->proxyCall($proxyHandler->get($action, $proxyHandler::MODEL_TYPE), $this, $args);
        }

        return parent::__call($action, $args);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string $action
     * @param  mixed  $args
     * @return void
     */
    public static function __callStatic($action, $args)
    {
        $proxyHandler = static::getProxyHandler();

        if ($proxyHandler->has($action, $proxyHandler::MODEL_TYPE)) {
            return static::getMeta()->proxyCall($proxyHandler->get($action, $proxyHandler::MODEL_TYPE), null, $args);
        }

        return parent::__callStatic($action, $args);
    }
}
