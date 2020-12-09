<?php
/**
 * Laramore model.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Contracts\Eloquent;

use ArrayAccess;
use JsonSerializable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Contracts\Queue\QueueableEntity;

interface LaramoreModel extends ArrayAccess, Arrayable, Jsonable, JsonSerializable, QueueableEntity, UrlRoutable
{
    /**
     * Allow the user to define all meta data for the current model.
     *
     * @param  LaramoreMeta $meta
     * @return mixed
     */
    public static function meta(LaramoreMeta $meta);

    /**
     * Generate one time the model meta.
     *
     * @param  LaramoreMeta $meta
     * @return void
     */
    public static function prepareMeta(LaramoreMeta $meta);

    /**
     * Return the meta class to use.
     *
     * @return string
     */
    public static function getMetaClass(): string;

    /**
     * Get the model meta.
     *
     * @return LaramoreMeta
     */
    public static function getMeta();

    /**
     * Get the value of the model's primary key.
     *
     * @return string|array Depending if the key is composed or not.
     */
    public function getKey();

    /**
     * Preset attributes.
     *
     * @return self
     */
    public function presetAttributes();

    /**
     * Unset a specific field.
     *
     * @param  mixed $key
     * @return self
     */
    public function unsetAttribute($key);

    /**
     * Unset all attributes.
     *
     * @return self
     */
    public function unsetAttributes();

    /**
     * Reset a specific field.
     *
     * @param  mixed $key
     * @return self
     */
    public function resetAttribute($key);

    /**
     * Reset all attributes.
     *
     * @return self
     */
    public function resetAttributes();

    /**
     * Return all attributes.
     *
     * @return array
     */
    public function getAttributes(): array;

    /**
     * Return all attributes from array.
     *
     * @return array
     */
    public function getAttributeValues(): array;

    /**
     * Indicate if it has a specific attribute.
     *
     * @param  mixed $key
     * @return boolean
     */
    public function hasAttribute($key): bool;

    /**
     * Indicate if it has a loaded attribute.
     *
     * @param  mixed $key
     * @return boolean
     */
    public function hasAttributeValue($key): bool;

    /**
     * Get the attribute value for a specific key.
     *
     * @param  mixed $key
     * @return mixed
     */
    public function getAttribute($key);

    /**
     * Get an attribute value.
     *
     * @param  mixed $key
     * @return mixed
     */
    public function getAttributeValue($key);

    /**
     * Return the attribute value from array.
     *
     * @param mixed $key
     * @return mixed
     */
    public function getAttributeFromArray($key);

    /**
     * Set an attribute on the model.
     *
     * @param mixed $key
     * @param  mixed $value
     * @return self
     */
    public function setAttribute($key, $value);

    /**
     * Set the given attributeship into the model array.
     *
     * @param mixed $key
     * @param  mixed $value
     * @return self
     */
    public function setAttributeValue($key, $value);

    /**
     * Unset a specific field.
     *
     * @param  mixed $key
     * @return self
     */
    public function unsetRelation($key);

    /**
     * Unset all relations.
     *
     * @return self
     */
    public function unsetRelations();

    /**
     * Reset a specific field.
     *
     * @param mixed $key
     * @return self
     */
    public function resetRelation($key);

    /**
     * Reset all relations.
     *
     * @return self
     */
    public function resetRelations();

    /**
     * Return all relations.
     *
     * @return array
     */
    public function getRelations(): array;

    /**
     * Return all relations from array.
     *
     * @return array
     */
    public function getRelationValues(): array;

    /**
     * Indicate if it has a specific relation.
     *
     * @param  mixed $key
     * @return boolean
     */
    public function hasRelation($key): bool;

    /**
     * Indicate if it has a loaded relation.
     *
     * @param  mixed $key
     * @return boolean
     */
    public function hasRelationValue($key): bool;

    /**
     * Get the relation value for a specific key.
     *
     * @param  mixed $key
     * @return mixed
     */
    public function getRelation($key);

    /**
     * Get a relationship.
     *
     * @param  mixed $key
     * @return mixed
     */
    public function getRelationValue($key);

    /**
     * Return the relation value from array.
     *
     * @param mixed $key
     * @return mixed
     */
    public function getRelationFromArray($key);

    /**
     * Set the given relationship on the model.
     *
     * @param  mixed $key
     * @param  mixed $value
     * @return self
     */
    public function setRelation($key, $value);

    /**
     * Set the given relationship into the model array.
     *
     * @param  mixed $key
     * @param  mixed $value
     * @return self
     */
    public function setRelationValue($key, $value);

    /**
     * Unset a specific field.
     *
     * @param  mixed $key
     * @return self
     */
    public function unsetExtra($key);

    /**
     * Unset all extras.
     *
     * @return self
     */
    public function unsetExtras();

    /**
     * Reset a specific field.
     *
     * @param  mixed $key
     * @return self
     */
    public function resetExtra($key);

    /**
     * Reset all extras.
     *
     * @return self
     */
    public function resetExtras();

    /**
     * Return all extras.
     *
     * @return array
     */
    public function getExtras(): array;

    /**
     * Return all extras from array.
     *
     * @return array
     */
    public function getExtraValues(): array;

    /**
     * Indicate if it has a specific extra.
     *
     * @param  mixed $key
     * @return boolean
     */
    public function hasExtra($key): bool;

    /**
     * Indicate if it has a loaded extra.
     *
     * @param  mixed $key
     * @return boolean
     */
    public function hasExtraValue($key): bool;

    /**
     * Get the extra value for a specific key.
     *
     * @param  mixed $key
     * @return mixed
     */
    public function getExtra($key);

    /**
     * Get a extraship.
     *
     * @param  mixed $key
     * @return mixed
     */
    public function getExtraValue($key);

    /**
     * Return the extra value from array.
     *
     * @param mixed $key
     * @return mixed
     */
    public function getExtraFromArray($key);

    /**
     * Set the given extraship on the model.
     *
     * @param  mixed $key
     * @param  mixed $value
     * @return self
     */
    public function setExtra($key, $value);

    /**
     * Set the given extraship into the model array.
     *
     * @param  mixed $key
     * @param  mixed $value
     * @return self
     */
    public function setExtraValue($key, $value);

    /**
     * Set the array of model attributes. No checking is done.
     *
     * @param  array $attributes
     * @param  mixed $sync
     * @return self
     */
    public function setRawAttributes(array $attributes, $sync=false);

    /**
     * Reload the current model instance with fresh attributes from the database.
     *
     * @param  array $attributes
     * @return self
     */
    public function refresh(array $attributes=['*']);

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @param  mixed $related
     * @param  mixed $foreignKey
     * @param  mixed $ownerKey
     * @param  mixed $relation
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsTo($related, $foreignKey=null, $ownerKey=null, $relation=null);

    /**
     * Define a many-to-many relationship.
     *
     * @param  mixed $related
     * @param  mixed $table
     * @param  mixed $foreignPivotKey
     * @param  mixed $relatedPivotKey
     * @param  mixed $parentKey
     * @param  mixed $relatedKey
     * @param  mixed $relation
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function belongsToMany($related, $table=null, $foreignPivotKey=null, $relatedPivotKey=null,
                                  $parentKey=null, $relatedKey=null, $relation=null);

    /**
     * Define a one-to-one relationship.
     *
     * @param  mixed $related
     * @param  mixed $foreignKey
     * @param  mixed $localKey
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOne($related, $foreignKey=null, $localKey=null);

    /**
     * Define a one-to-many relationship.
     *
     * @param  mixed $related
     * @param  mixed $foreignKey
     * @param  mixed $localKey
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasMany($related, $foreignKey=null, $localKey=null);

    /**
     * Define a polymorphic, inverse one-to-one or many relationship.
     *
     * @param  string|mixed $name
     * @param  string|mixed $type
     * @param  string|mixed $id
     * @param  string|mixed $ownerKey
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function morphTo($name=null, $type=null, $id=null, $ownerKey=null);

    /**
     * Get a new query builder that doesn't have any global scopes or eager loading.
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newModelQuery();

    /**
     * Create a new Eloquent Collection instance.
     *
     * @param  array $models
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function newCollection(array $models=[]);
}
