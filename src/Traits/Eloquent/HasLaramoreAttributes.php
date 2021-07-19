<?php
/**
 * Inject in models auto fields and relations management.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Traits\Eloquent;

use Illuminate\Database\Eloquent\MassAssignmentException;
use Laramore\Contracts\Field\{
    AttributeField, RelationField, ExtraField
};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Laramore\Elements\Element;
use Laramore\Eloquent\Relations\MorphTo;

trait HasLaramoreAttributes
{
    /**
     * Extra attributes.
     *
     * @var array
     */
    protected $extras = [];

    /**
     * Update the creation and update timestamps.
     *
     * @return void
     */
    public function updateTimestamps()
    {
        $time = $this->freshTimestamp();

        // @var \Illuminate\Database\Eloquent\Model
        $class = static::class;

        if (!$this->exists && !\is_null($class::CREATED_AT) && !$this->isDirty($class::CREATED_AT)) {
            $this->setRawAttributes([$class::CREATED_AT => $time]);
        }

        // Only update the updated field if the model already exists or the field cannot be null.
        if (!\is_null($class::UPDATED_AT) && !$this->isDirty($class::UPDATED_AT) && (
            $this->exists || !static::getMeta()->getField($class::UPDATED_AT)->nullable
        )) {
            $this->setRawAttributes([$class::UPDATED_AT => $time]);
        }
    }

    /**
     * Preset attributes.
     *
     * @return self
     */
    public function presetAttributes()
    {
        $this->resetAttributes();
        $this->resetRelations();
        $this->resetExtras();

        return $this;
    }

    /**
     * Unset a specific field.
     *
     * @param  mixed $key
     * @return self
     */
    public function unsetAttribute($key)
    {
        unset($this->attributes[$key]);

        return $this;
    }

    /**
     * Unset all attributes.
     *
     * @return self
     */
    public function unsetAttributes()
    {
        $this->attributes = [];

        return $this;
    }

    /**
     * Reset a specific field.
     *
     * @param  mixed $key Name of the field.
     * @return self
     */
    public function resetAttribute($key)
    {
        if (static::getMeta()->hasField($key)) {
            $field = static::getMeta()->getField($key);

            $field->getOwner()->resetFieldValue($field, $this);
        } else {
            $this->unsetAttribute($key);
        }

        return $this;
    }

    /**
     * Reset all attributes.
     *
     * @return self
     */
    public function resetAttributes()
    {
        foreach (static::getMeta()->getFields() as $field) {
            $field->getOwner()->resetFieldValue($field, $this);
        }

        return $this;
    }

    /**
     * Return all attributes.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        $attributes = [];

        foreach (\array_keys($this->getAttributeValues()) as $key) {
            $attributes[$key] = $this->getAttribute($key);
        }

        return $attributes;
    }

    /**
     * Return all attributes from array.
     *
     * @return array
     */
    public function getAttributeValues(): array
    {
        return $this->attributes;
    }

    /**
     * Indicate if it has a specific attribute.
     *
     * @param  mixed $key
     * @return boolean
     */
    public function hasAttribute($key): bool
    {
        if (static::getMeta()->hasField($key)) {
            $field = static::getMeta()->getField($key);

            return $field->getOwner()->hasFieldValue($field, $this);
        }

        return $this->hasAttributeValue($key);
    }

    /**
     * Indicate if it has a loaded attribute.
     *
     * @param  mixed $key
     * @return boolean
     */
    public function hasAttributeValue($key): bool
    {
        return \array_key_exists($key, $this->attributes);
    }

    /**
     * Get the attribute value for a specific key.
     *
     * @param  string|mixed $key Not specified because Model has no parameter types.
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (static::getMeta()->hasField($key)) {
            $field = static::getMeta()->getField($key);

            if (!($field instanceof AttributeField) || !$this->hasGetMutator($key)) {
                return $field->getOwner()->getFieldValue($field, $this);
            }
        }

        // If a relation is defined with this key, return it (ex: pivot).
        if ($this->hasRelation($key)) {
            return $this->getRelation($key);
        }

        // Else, simply return the extra value.
        return $this->getExtra($key);
    }

    /**
     * Get an attribute value.
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

        // If the key already exists in the attributes array, return it.
        if ($this->hasAttributeValue($key)) {
            return $this->getAttributeFromArray($key);
        }

        // If the user did not set any custom methods to handle this attribute,
        // we call the field getter.
        if (static::getMeta()->hasField($key, AttributeField::class)) {
            $field = static::getMeta()->getField($key, AttributeField::class);

            return tap($field->getOwner()->retrieveFieldValue($field, $this), function ($results) use ($key) {
                $this->setAttributeValue($key, $results);
            });
        }
    }

    /**
     * Get the value of an attribute using its mutator.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function mutateAttribute($key, $value)
    {
        if ($this->hasGetMutator($key)) {
            return parent::mutateAttribute($key, $value);
        }

        return $this->getAttribute($key);
    }

    /**
     * Return the attribute value from array.
     *
     * @param mixed $key
     * @return mixed
     */
    public function getAttributeFromArray($key)
    {
        if ($this->hasAttributeValue($key)) {
            return $this->attributes[$key];
        }
    }

    /**
     * Set an attribute on the model.
     *
     * @param  string|mixed $key
     * @param  mixed        $value
     * @return self
     */
    public function setAttribute($key, $value)
    {
        if (static::getMeta()->hasField($key)) {
            // If the field is not fillable, throw an exception.
            if (!$this->isFillable($key)) {
                throw new MassAssignmentException(sprintf(
                    'Add [%s] to fillable property to allow mass assignment on [%s].',
                    $key,
                    get_class($this)
                ));
            }

            $field = static::getMeta()->getField($key);

            $field->getOwner()->setFieldValue($field, $this, $value);
        } else {
            $this->setExtraValue($key, $value);
        }

        return $this;
    }

    /**
     * Set the given attributeship into the model array.
     *
     * @param  string|mixed $key
     * @param  mixed        $value
     * @return self
     */
    public function setAttributeValue($key, $value)
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Unset a specific field.
     *
     * @param  mixed $key
     * @return self
     */
    public function unsetRelation($key)
    {
        unset($this->relations[$key]);

        return $this;
    }

    /**
     * Unset all relations.
     *
     * @return self
     */
    public function unsetRelations()
    {
        $this->relations = [];

        return $this;
    }

    /**
     * Reset a specific field.
     *
     * @param  string|mixed $key Name of the field.
     * @return self
     */
    public function resetRelation($key)
    {
        if (static::getMeta()->hasField($key, RelationField::class)) {
            $field = static::getMeta()->getField($key, RelationField::class);

            $field->getOwner()->resetFieldValue($field, $this);
        } else {
            $this->unsetRelation($key);
        }

        return $this;
    }

    /**
     * Reset all relations.
     *
     * @return self
     */
    public function resetRelations()
    {
        $this->relations = [];

        foreach (static::getMeta()->getFields(RelationField::class) as $field) {
            $field->getOwner()->resetFieldValue($field, $this);
        }

        return $this;
    }

    /**
     * Return all relations.
     *
     * @return array
     */
    public function getRelations(): array
    {
        $relations = [];

        foreach (\array_keys($this->getRelationValues()) as $key) {
            $relations[$key] = $this->getRelation($key);
        }

        return $relations;
    }

    /**
     * Return all relations from array.
     *
     * @return array
     */
    public function getRelationValues(): array
    {
        return $this->relations;
    }

    /**
     * Indicate if it has a specific relation.
     *
     * @param  string|mixed $key
     * @return boolean
     */
    public function hasRelation($key): bool
    {
        if (static::getMeta()->hasField($key, RelationField::class)) {
            $field = static::getMeta()->getField($key, RelationField::class);

            return $field->getOwner()->hasFieldValue($field, $this);
        }

        return $this->hasRelationValue($key) || \method_exists($this, $key);
    }

    /**
     * Indicate if it has a loaded relation.
     *
     * @param  string|mixed $key
     * @return boolean
     */
    public function hasRelationValue($key): bool
    {
        return \array_key_exists($key, $this->relations);
    }

    /**
     * Get the relation value for a specific key.
     *
     * @param  string|mixed $key Not specified because Model has no parameter types.
     * @return mixed
     */
    public function getRelation($key)
    {
        if (static::getMeta()->hasField($key, RelationField::class)) {
            // If the field is not fillable, throw an exception.
            if (!$this->isFillable($key)) {
                throw new MassAssignmentException(sprintf(
                    'Add [%s] to fillable property to allow mass assignment on [%s].',
                    $key,
                    get_class($this)
                ));
            }

            $field = static::getMeta()->getField($key, RelationField::class);

            return $field->getOwner()->getFieldValue($field, $this);
        }

        return $this->getRelationValue($key);
    }

    /**
     * Get a relationship.
     *
     * @param  string|mixed $key
     * @return mixed
     */
    public function getRelationValue($key)
    {
        // If the key already exists in the relationships array, it just means the
        // relationship has already been locked, so we'll just return it out of
        // here because there is no need to query within the relations twice.
        if ($this->hasRelationValue($key)) {
            return $this->getRelationFromArray($key);
        }

        // If the "attribute" exists as a method on the model, we will just assume
        // it is a relationship and will load and return results from the query
        // and hydrate the relationship's value on the "relationships" array.
        if (\method_exists($this, $key)) {
            debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            return $this->getRelationshipFromMethod($key);
        }

        // If the user did not set any custom methods to handle this attribute,
        // we call the field getter.
        if (static::getMeta()->hasField($key, RelationField::class)) {
            $field = static::getMeta()->getField($key, RelationField::class);

            return tap($field->getOwner()->retrieveFieldValue($field, $this), function ($results) use ($key) {
                $this->setRelationValue($key, $results);
            });
        }
    }

    /**
     * Return the relation value from array.
     *
     * @param mixed $key
     * @return mixed
     */
    public function getRelationFromArray($key)
    {
        if ($this->hasRelationValue($key)) {
            return $this->relations[$key];
        }
    }

    /**
     * Set the given relationship on the model.
     *
     * @param  string|mixed $key
     * @param  mixed        $value
     * @return self
     */
    public function setRelation($key, $value)
    {
        if (static::getMeta()->hasField($key, RelationField::class)) {
            $field = static::getMeta()->getField($key, RelationField::class);

            $field->getOwner()->setFieldValue($field, $this, $value);
        } else {
            $this->setRelationValue($key, $value);
        }

        return $this;
    }

    /**
     * Set the given relationship into the model array.
     *
     * @param  string|mixed $key
     * @param  mixed        $value
     * @return self
     */
    public function setRelationValue($key, $value)
    {
        $this->relations[$key] = $value;

        return $this;
    }

    /**
     * Unset a specific field.
     *
     * @param  mixed $key
     * @return self
     */
    public function unsetExtra($key)
    {
        unset($this->extras[$key]);

        return $this;
    }

    /**
     * Unset all extras.
     *
     * @return self
     */
    public function unsetExtras()
    {
        $this->extras = [];

        return $this;
    }

    /**
     * Reset a specific field.
     *
     * @param  string|mixed $key Name of the field.
     * @return self
     */
    public function resetExtra($key)
    {
        if (static::getMeta()->hasField($key, ExtraField::class)) {
            $field = static::getMeta()->getField($key, ExtraField::class);

            $field->getOwner()->resetFieldValue($field, $this);
        } else {
            $this->unsetExtra($key);
        }

        return $this;
    }

    /**
     * Reset all extras.
     *
     * @return self
     */
    public function resetExtras()
    {
        $this->extras = [];

        foreach (static::getMeta()->getFields(ExtraField::class) as $field) {
            $field->getOwner()->resetFieldValue($field, $this);
        }

        return $this;
    }

    /**
     * Return all extras.
     *
     * @return array
     */
    public function getExtras(): array
    {
        $extras = [];

        foreach (\array_keys($this->getExtraValues()) as $key) {
            $extras[$key] = $this->getExtra($key);
        }

        return $extras;
    }

    /**
     * Return all extras from array.
     *
     * @return array
     */
    public function getExtraValues(): array
    {
        return $this->extras;
    }

    /**
     * Indicate if it has a specific extra.
     *
     * @param  string|mixed $key
     * @return boolean
     */
    public function hasExtra($key): bool
    {
        if (static::getMeta()->hasField($key)) {
            $field = static::getMeta()->getField($key);

            if (!($field instanceof RelationField) && !($field instanceof AttributeField)) {
                return $field->getOwner()->hasFieldValue($field, $this);
            }
        }

        return $this->hasExtraValue($key) || $this->hasMutator($this, $key);
    }

    /**
     * Indicate if it has a loaded extra.
     *
     * @param  string|mixed $key
     * @return boolean
     */
    public function hasExtraValue($key): bool
    {
        return \array_key_exists($key, $this->extras);
    }

    /**
     * Get the extra value for a specific key.
     *
     * @param  string|mixed $key Not specified because Model has no parameter types.
     * @return mixed
     */
    public function getExtra($key)
    {
        if (static::getMeta()->hasField($key)) {
            $field = static::getMeta()->getField($key);

            if ($field instanceof RelationField || $field instanceof AttributeField) {
                throw new \LogicException("The field `$key` cannot be get via `getExtra`");
            }

            return $field->getOwner()->getFieldValue($field, $this);
        }

        return $this->getExtraValue($key);
    }

    /**
     * Get a extraship.
     *
     * @param  mixed $key
     * @return mixed
     */
    public function getExtraValue($key)
    {
        // If the attribute has a get mutator, we will call that then return what
        // it returns as the value, which is useful for transforming values on
        // retrieval from the model to a form that is more useful for usage.
        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $this->getExtraFromArray($key));
        }

        // If the key already exists in the extraships array, it just means the
        // extraship has already been locked, so we'll just return it out of
        // here because there is no need to query within the extras twice.
        if ($this->hasExtraValue($key)) {
            return $this->getExtraFromArray($key);
        }

        // If the user did not set any custom methods to handle this attribute,
        // we call the field getter.
        if (static::getMeta()->hasField($key, ExtraField::class)) {
            $field = static::getMeta()->getField($key, ExtraField::class);

            if ($field instanceof RelationField) {
                throw new \LogicException("The field `$key` cannot be get via `getExtraValue` but only via `getRelationValue`");
            }

            return tap($field->getOwner()->retrieveFieldValue($field, $this), function ($results) use ($key) {
                $this->setExtraValue($key, $results);
            });
        }
    }

    /**
     * Return the extra value from array.
     *
     * @param  string|mixed $key
     * @return mixed
     */
    public function getExtraFromArray($key)
    {
        if ($this->hasExtraValue($key)) {
            return $this->extras[$key];
        }
    }

    /**
     * Set the given extraship on the model.
     *
     * @param  string|mixed $key
     * @param  mixed        $value
     * @return self
     */
    public function setExtra($key, $value)
    {
        if (static::getMeta()->hasField($key, ExtraField::class)) {
            // If the field is not fillable, throw an exception.
            if (!$this->isFillable($key)) {
                throw new MassAssignmentException(sprintf(
                    'Add [%s] to fillable property to allow mass assignment on [%s].',
                    $key,
                    get_class($this)
                ));
            }

            $field = static::getMeta()->getField($key, ExtraField::class);

            if ($field instanceof RelationField) {
                throw new \LogicException("The field `$key` cannot be set via `setExtra` but only via `setRelation`");
            }

            $field->getOwner()->setFieldValue($field, $this, $value);
        } else {
            $this->setExtraValue($key, $value);
        }

        return $this;
    }

    /**
     * Set the given extraship into the model array.
     *
     * @param  string|mixed $key
     * @param  mixed        $value
     * @return self
     */
    public function setExtraValue($key, $value)
    {
        $this->extras[$key] = $value;

        return $this;
    }

    /**
     * Set the array of model attributes. No checking is done.
     *
     * @param  array $attributes
     * @param  mixed $sync
     * @return self
     */
    public function setRawAttributes(array $attributes, $sync=false)
    {
        foreach ($attributes as $key => $value) {
            if (static::getMeta()->hasField($key)) {
                $field = static::getMeta()->getField($key);

                $field->getOwner()->setFieldValue($field, $this, $value);
            } else {
                $this->setExtraValue($key, $value);
            }
        }

        if ($sync) {
            $this->syncOriginal();
        }

        return $this;
    }

    /**
     * Retrieve the actual class name for a given morph class.
     *
     * @param  string|mixed $class
     * @return string
     */
    public static function getActualClassNameForMorph($class)
    {
        if ($class instanceof Element) {
            $class = $class->getName();
        }

        return Arr::get(Relation::morphMap() ?: [], $class, $class);
    }

    /**
     * Instantiate a new MorphTo relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  \Illuminate\Database\Eloquent\Model   $parent
     * @param  string|mixed                          $foreignKey
     * @param  string|mixed                          $ownerKey
     * @param  string|mixed                          $type
     * @param  string|mixed                          $relation
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    protected function newMorphTo(Builder $query, Model $parent, $foreignKey, $ownerKey, $type, $relation)
    {
        return new MorphTo($query, $parent, $foreignKey, $ownerKey, $type, $relation);
    }

    /**
     * Insert the given attributes and set the ID on the model.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array|mixed                           $attributes
     * @return void
     */
    protected function insertAndSetId(Builder $query, $attributes)
    {
        $ids = $query->insertGetId($attributes, $keys = $this->getKeyName());

        $this->setRawAttributes(\array_combine(
            \is_array($keys) ? $keys : [$keys],
            \is_array($ids) ? $ids : [$ids]
        ));
    }

    /**
     * Reload the current model instance with fresh attributes from the database.
     *
     * @param  array $attributes
     * @return self
     */
    public function refresh(array $attributes=['*'])
    {
        if (!$this->exists) {
            return $this;
        }

        $this->setRawAttributes(
            static::newQueryWithoutScopes()->findOrFail($this->getKey(), $attributes)->attributes
        );

        $this->load(collect($this->relations)->except('pivot')->keys()->toArray());

        if ($attributes === ['*']) {
            $this->syncOriginal();
        } else {
            foreach ($attributes as $attribute) {
                $this->syncOriginalAttribute($attribute);
            }
        }

        return $this;
    }

    /**
     * Get an attribute array of all \ArrayAccess extras.
     *
     * @return array
     */
    protected function getArrayableExtras()
    {
        return $this->getArrayableItems($this->extras);
    }

    /**
     * Convert the model's attributes to an array.
     *
     * @return array
     */
    public function attributesToArray()
    {
        $attributes = $this->getArrayableAttributes();

        $attributes = $this->addMutatedAttributesToArray(
            $attributes,
            $mutatedAttributes = $this->getMutatedAttributes()
        );

        // Next we will handle any casts that have been setup for this model and cast
        // the values to their appropriate type. If the attribute has a mutator we
        // will not perform the cast on those attributes to avoid any confusion.
        $attributes = $this->addCastAttributesToArray(
            $attributes,
            $mutatedAttributes
        );

        foreach ($attributes as $key => $value) {
            if (static::getMeta()->hasField($key, AttributeField::class)) {
                $field = static::getMeta()->getField($key, AttributeField::class);
                $attributes[$key] = $field->getOwner()->serializeFieldValue($field, $value);
            }
        }

        return $attributes;
    }

    /**
     * Convert the model's relations to an array.
     *
     * @return array
     */
    public function relationsToArray()
    {
        $relations = parent::relationsToArray();

        foreach ($relations as $key => $value) {
            if (static::getMeta()->hasField($key, RelationField::class)) {
                $field = static::getMeta()->getField($key, RelationField::class);

                $relations[$key] = $field->getOwner()->serializeFieldValue($field, $value);
            }
        }

        return $relations;
    }

    /**
     * Convert the model's extras to an array.
     *
     * @return array
     */
    public function extrasToArray()
    {
        $extras = $this->getArrayableExtras();

        // Here we will grab all of the appended, calculated attributes to this model
        // as these attributes are not really in the attributes array, but are run
        // when we need to array or JSON the model for convenience to the coder.
        foreach ($this->getArrayableAppends() as $key) {
            $extras[$key] = $this->mutateAttributeForArray($key, null);
        }

        foreach ($extras as $key => $value) {
            if (static::getMeta()->hasField($key, ExtraField::class)) {
                $field = static::getMeta()->getField($key, ExtraField::class);

                $attributes[$key] = $field->getOwner()->serializeFieldValue($field, $value);
            }
        }

        return $extras;
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $data = array_merge($this->attributesToArray(), $this->relationsToArray(), $this->extrasToArray());
        $attributes = [];

        foreach (\array_keys(static::getMeta()->getFields()) as $key) {
            if (isset($data[$key])) {
                $attributes[$key] = $data[$key];

                unset($data[$key]);
            }
        }

        return \array_merge($attributes, $data);
    }

    /**
     * Unset the value for a given offset.
     *
     * @param  mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->unsetAttribute($offset);
        $this->unsetRelation($offset);
        $this->unsetExtra($offset);
    }
}
