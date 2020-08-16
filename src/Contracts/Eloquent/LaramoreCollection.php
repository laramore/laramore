<?php
/**
 * Laramore collection for model collections.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Contracts\Eloquent;

use Illuminate\Contracts\Queue\QueueableCollection;

interface LaramoreCollection extends QueueableCollection
{
    /**
     * Set all models as fetching.
     *
     * @param boolean $fetching
     * @return self
     */
    public function fetching(bool $fetching=true);

    /**
     * Find a model in the collection by key.
     *
     * @param  mixed $key
     * @param  mixed $default
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function find($key, $default=null);

    /**
     * Load a set of relationships onto the collection.
     *
     * @param  mixed $relations
     * @return self
     */
    public function load($relations);

    /**
     * Determine if a key exists in the collection.
     *
     * @param  mixed $key
     * @param  mixed $operator
     * @param  mixed $value
     * @return boolean
     */
    public function contains($key, $operator=null, $value=null);

    /**
     * Get the array of primary keys.
     *
     * @return array
     */
    public function modelKeys();

    /**
     * Run a map over each of the items.
     *
     * @param  callable $callback
     * @return \Illuminate\Support\Collection|static
     */
    public function map(callable $callback);

    /**
     * Reload a fresh model instance from the database for all the entities.
     *
     * @param  array|string $with
     * @return static
     */
    public function fresh($with=[]);

    /**
     * Make the given, typically visible, attributes hidden across the entire collection.
     *
     * @param  array|string $attributes
     * @return self
     */
    public function makeHidden($attributes);

    /**
     * Make the given, typically hidden, attributes visible across the entire collection.
     *
     * @param  array|string $attributes
     * @return self
     */
    public function makeVisible($attributes);

    /**
     * Get an array with the values of a given key.
     *
     * @param  mixed       $value
     * @param  string|null $key
     * @return \Illuminate\Support\Collection
     */
    public function pluck($value, $key=null);

    /**
     * Get the keys of the collection items.
     *
     * @return \Illuminate\Support\Collection
     */
    public function keys();
}
