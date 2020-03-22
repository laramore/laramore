<?php
/**
 * Inject in models auto fields and relations management.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Traits\Model;

use Illuminate\Database\Eloquent\MassAssignmentException;
use Laramore\Contracts\Field\{
    AttributeField, RelationField, ExtraField
};
use Illuminate\Database\Eloquent\Builder;

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
    protected function updateTimestamps()
    {
        $time = $this->freshTimestamp();

        if (!$this->exists && !\is_null(static::CREATED_AT) && !$this->isDirty(static::CREATED_AT)) {
            $this->setRawAttributes([static::CREATED_AT => $time]);
        }

        // Only update the updated field if the model already exists or the field cannot be null.
        if (!\is_null(static::UPDATED_AT) && !$this->isDirty(static::UPDATED_AT) && (
            $this->exists || !static::getMeta()->getField(static::UPDATED_AT)->nullable
        )) {
            $this->setRawAttributes([static::UPDATED_AT => $time]);
        }
    }

    /**
     * Reset a specific field.
     *
     * @param  $key Name of the field.
     * @return self
     */
    public function resetAttribute($key)
    {
        if (static::getMeta()->hasField($key)) {
            $field = static::getMeta()->getField($key);

            $field->getOwner()->resetFieldValue($field, $this);
        } else {
            unset($this->attributes[$key]);
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
        $this->attributes = [];

        foreach (static::getMeta()->getFields(AttributeField::class) as $field) {
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
        return $this->hasAttributeValue($key)
            || static::getMeta()->hasField($key, AttributeField::class);
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
     * @param  $key Not specified because Model has no parameter types.
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

        return $this->getExtraValue($key);
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
        if (static::getMeta()->hasField($key, AttributeField::class) && $this->hasGetMutator($key)) {
            $field = static::getMeta()->getField($key);

            return $this->mutateAttribute($key, $field->getOwner()->getFieldValue($field, $this));
        }

        return $this->getAttributeFromArray($key);
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
     * @param  $key
     * @param  mixed $value
     * @return $this
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

            return $this;
        }

        $this->setExtraValue($key, $value);

        return $this;
    }

    /**
     * Set the given attributeship into the model array.
     *
     * @param  $key
     * @param  mixed $value
     * @return $this
     */
    public function setAttributeValue($key, $value)
    {
        if (static::getMeta()->hasField($key, AttributeField::class)) {
            $field = static::getMeta()->getField($key, AttributeField::class);

            $this->attributes[$key] = $field->getOwner()->castFieldValue($field, $value);
        } else {
            $this->attributes[$key] = $value;
        }

        return $this;
    }

    /**
     * Reset a specific field.
     *
     * @param  $key Name of the field.
     * @return self
     */
    public function resetRelation($key)
    {
        if (static::getMeta()->hasField($key, RelationField::class)) {
            $field = static::getMeta()->getField($key, RelationField::class);

            $field->getOwner()->resetFieldValue($field, $this);
        } else {
            unset($this->relations[$key]);
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
     * @return boolean
     */
    public function hasRelation($key): bool
    {
        return $this->hasRelationValue($key)
            || \method_exists($this, $key)
            || static::getMeta()->hasField($key, RelationField::class);
    }

    /**
     * Indicate if it has a loaded relation.
     *
     * @return boolean
     */
    public function hasRelationValue($key): bool
    {
        return \array_key_exists($key, $this->relations);
    }

    /**
     * Get the relation value for a specific key.
     *
     * @param  $key Not specified because Model has no parameter types.
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
     * @param  mixed $key
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
     * @param  $key
     * @param  mixed $value
     * @return $this
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
     * @param  $key
     * @param  mixed $value
     * @return $this
     */
    public function setRelationValue($key, $value)
    {
        if (static::getMeta()->hasField($key, RelationField::class)) {
            $field = static::getMeta()->getField($key, RelationField::class);

            $this->relations[$key] = $field->getOwner()->castFieldValue($field, $value);
        } else {
            $this->relations[$key] = $value;
        }

        return $this;
    }

    /**
     * Reset a specific field.
     *
     * @param  $key Name of the field.
     * @return self
     */
    public function resetExtra($key)
    {
        if (static::getMeta()->hasField($key, ExtraField::class)) {
            $field = static::getMeta()->getField($key, ExtraField::class);

            $field->getOwner()->resetFieldValue($field, $this);
        } else {
            unset($this->extras[$key]);
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
     * @return boolean
     */
    public function hasExtra($key): bool
    {
        return $this->hasExtraValue($key)
            || $this->hasMutator($this, $key)
            || (static::getMeta()->hasField($key)
                && !($field = static::getMeta()->getField($key) instanceof RelationField)
                && !($field instanceof AttributeField));
    }

    /**
     * Indicate if it has a loaded extra.
     *
     * @return boolean
     */
    public function hasExtraValue($key): bool
    {
        return \array_key_exists($key, $this->extras);
    }

    /**
     * Get the extra value for a specific key.
     *
     * @param  $key Not specified because Model has no parameter types.
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
     * @param mixed $key
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
     * @param  $key
     * @param  mixed $value
     * @return $this
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
     * @param  $key
     * @param  mixed $value
     * @return $this
     */
    public function setExtraValue($key, $value)
    {
        if (static::getMeta()->hasField($key, ExtraField::class)) {
            $field = static::getMeta()->getField($key, ExtraField::class);

            $this->extras[$key] = $field->getOwner()->castFieldValue($field, $value);
        } else {
            $this->extras[$key] = $value;
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

        if (\is_null($relations)) {
            $relationsToSave = $this->relations;
        } else {
            $relationsToSave = \array_intersect_key($this->relations, \array_flip($relations));
        }

        foreach ($relationsToSave as $key => $relation) {
            $field = static::getMeta()->getField($key);

            $status = $status && $field->getOwner()->reverbateFieldValue($field, $this, $relation);
        }

        return $status;
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
        $ids = $query->insertGetId($attributes, $keys = $this->getKeyName());

        $this->setRawAttributes(\array_combine($keys, \is_array($ids) ? $ids : [$ids]));
    }

    /**
     * Get an attribute array of all arrayable extras.
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
        // If an attribute is a date, we will cast it to a string after converting it
        // to a DateTime / Carbon instance. This is so we will get some consistent
        // formatting while accessing attributes vs. arraying / JSONing a model.
        $attributes = $this->addDateAttributesToArray(
            $attributes = $this->getArrayableAttributes()
        );

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
}
