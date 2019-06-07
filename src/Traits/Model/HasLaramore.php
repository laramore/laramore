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

use Laramore\Facades\TypeManager;
use Laramore\Fields\{
    Field, CompositeField, LinkField
};
use Laramore\{
    Meta, FieldManager, Builder
};
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\MassAssignmentException;

trait HasLaramore
{
    protected static $meta;

    protected $required = [];

    /**
     * Prepare the model during the creation of the object.
     * Add by default fillable fields, visible fields and the primary key.
     *
     * @param mixed ...$args
     */
    public function __construct(...$args)
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
        $this->setKeyName($meta->getPrimary()->attname);
        $this->setIncrementing($meta->getPrimary()->type === TypeManager::getType('increment'));
        $this->setTable($meta->getTableName());

        parent::__construct(...$args);
    }

    /**
     * Allow the user to define all metas for the current model.
     *
     * @param  Meta $meta All model meta data.
     * @return void
     */
    abstract protected static function __meta(Meta $meta);

    /**
     * Generate one time the model meta.
     *
     * @return void
     */
    protected static function prepareMeta()
    {
        static::$meta = new Meta(static::class);

        // Generate all meta data defined by the user in the current model.
        static::__meta(static::$meta);
    }

    /**
     * Get the model meta.
     *
     * @return Meta
     */
    public static function getMeta()
    {
        if (!static::$meta) {
            static::prepareMeta();
        }

        return static::$meta;
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
        return static::getMeta()->allFields();
    }

    /**
     * Cast and check a value for a specific key.
     *
     * @param  string $key   Name of the field.
     * @param  mixed  $value
     * @return mixed		 The casted value.
     */
    public function cast(string $key, $value)
    {
        if ($this->hasField($key)) {
            return $this->getField($key)->castValue($this, $value);
        }
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
        $value = $this->getAttributeFromArray($key);

        // If the attribute has a get mutator, we will call that then return what
        // it returns as the value, which is useful for transforming values on
        // retrieval from the model to a form that is more useful for usage.
        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $value);
        }

        // If the attribute exists within the cast array, we will convert it to
        // an appropriate native PHP type dependant upon the associated value
        // given with the key in the pair. Dayle made this comment line up.
        if ($this->hasCast($key)) {
            return $this->castAttribute($key, $value);
        }

        // If the attribute is listed as a date, we will convert it to a DateTime
        // instance on retrieval, which makes it quite convenient to work with
        // date fields without having to create a mutator for each property.
        if (in_array($key, $this->getDates()) && !is_null($value)) {
            return $this->asDateTime($value);
        }

        $key = Str::snake($key);

        // If the user did not set any custom methods to handle this attribute,
        // we call the field getter.
        if (static::hasField($key)) {
            $field = static::getField($key);

            return $field->getOwner()->getFieldValue($this, $field, $value);
        }

        return $value;
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
            return $this->relations[$key];
        }

        // If the "attribute" exists as a method on the model, we will just assume
        // it is a relationship and will lock and return results from the query
        // and hydrate the relationship's value on the "relationships" array.
        if (method_exists($this, $key)) {
            return $this->getRelationshipFromMethod($key);
        }

        // Check if a composite of link field exist with this name and return the relation.
        if (static::getMeta()->hasComposite($key) || static::getMeta()->hasLink($key)) {
            return $this->getRelationshipFromMeta($key);
        }
    }

    /**
     * Get a relationship value from the meta.
     *
     * @param  mixed $key
     * @return mixed
     *
     * @throws \LogicException Except if the relation does not exist.
     */
    protected function getRelationshipFromMeta($key)
    {
        return tap(static::getMeta()->get($key)->getValue($this, $key), function ($results) use ($key) {
            $this->setRelation($key, $results);
        });
    }

    /**
     * Set a given attribute on the model.
     * Override the original method.
     *
     * @param  mixed   $key
     * @param  mixed   $value
     * @param  boolean $force
     * @return mixed
     *
     * @throws Exception Except if the field is not fillable.
     */
    public function setAttribute($key, $value, bool $force=false)
    {
        if ($this->hasSetMutator($key)) {
            // First we will check for the presence of a mutator for the set operation
            // which simply lets the developers tweak the attribute as it is set on
            // the model, such as "json_encoding" an listing of data for storage.
            return $this->setMutatedAttributeValue($key, $value);
        } else if ($value && $this->isDateAttribute($key)) {
            // If an attribute is listed as a "date", we'll convert it from a DateTime
            // instance into a form proper for storage on the database tables using
            // the connection grammar's date format. We will auto set the values.
            $this->attributes[$key] = $this->fromDateTime($value);

            return $this;
        }

        if ($this->isJsonCastable($key) && ! is_null($value)) {
            $this->attributes[$key] = $this->castAttributeAsJson($key, $value);

            return $this;
        }

        // If this attribute contains a JSON ->, we'll set the proper value in the
        // attribute's underlying array. This takes care of properly nesting an
        // attribute in the array's value in the case of deeply nested items.
        if (Str::contains($key, '->')) {
            return $this->fillJsonAttribute($key, $value);
        }

        $key = Str::snake($key);

        // Check if the field exists to cast the value.
        if (static::hasField($key)) {
            $field = static::getField($key);

            // If the field is not fillable, throw an exception.
            if ($field instanceof Field && !$this->isFillable($key) && !$force) {
                throw new MassAssignmentException(sprintf(
                    'Add [%s] to fillable property to allow mass assignment on [%s].',
                    $key, get_class($this)
                ));
            }

            $value = $field->getOwner()->setFieldValue($this, $field, $value);

            if ($field instanceof Field) {
                $this->attributes[$key] = $value;
            }
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
            $this->setAttribute($key, $value, true);
        }

        if ($sync) {
            $this->syncOriginal();
        }

        return $this;
    }

    /**
     * Insert the given attributes and set the ID on the model.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  mixed                                 $attributes
     * @return void
     */
    protected function insertAndSetId(\Illuminate\Database\Eloquent\Builder $query, $attributes)
    {
        $id = $query->insertGetId($attributes, $keyName = $this->getKeyName());

        $this->setAttribute($keyName, $id, true);
    }

    /**
     * Get the relation value for a specific key.
     *
     * @param  string $key
     * @return mixed
     */
    public function relation(string $key)
    {
        return static::getField($key)->relationValue($this);
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
        return new Builder($query);
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

    /**
     * Handle dynamically unknown calls.
     * - {fieldName}(): Returns the relation with the field.
     * - {fieldName}(...$args): Set the field value for this instance.
     * - {anyMethod}{FieldName}(...$args): Returns the value of the field method {anyMethod}Value.
     *
     * @param  mixed $method
     * @param  mixed $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (static::hasField($method)) {
            $field = static::getField($method);

            if (count($args) === 0) {
                return $field->relationValue($this);
            } else {
                return $field->setValue($this, ...$args);
            }
        } else {
            $parts = explode('_', Str::snake($method));
            $fieldName = '';

            while (count($parts) > 1) {
                $fieldName = array_pop($parts).Str::studly($fieldName);

                if (static::getMeta()->has($fieldName)) {
                    $field = static::getMeta()->get($fieldName);
                    $name = Str::camel(implode('_', $parts)).'Value';

                    if (\method_exists($field, $name)) {
                        return $field->$name($this, $this->{$fieldName}, ...$args);
                    } else {
                        throw new \Exception("The method $method does not exists for the field $name");
                    }
                }
            }
        }

        return parent::__call($method, $args);
    }
}
