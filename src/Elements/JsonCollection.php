<?php
/**
 * Define a specific json field element.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Elements;

use Illuminate\Support\Collection;

class JsonCollection extends Collection
{
    /**
     * Resolve the offset key.
     *
     * @param mixed $key
     * @return array
     */
    public function resolveOffset($key)
    {
        return explode('->', $key);
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param  mixed $key
     * @return boolean
     */
    public function offsetExists($key)
    {
        $path = $this->resolveOffset($key);
        $json = $this->items;

        foreach ($path as $subKey) {
            if (!array_key_exists($subKey, $json)) {
                return false;
            }

            $json = $json[$subKey];
        }

        return true;
    }

    /**
     * Get an item at a given offset.
     *
     * @param  mixed $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        $path = $this->resolveOffset($key);
        $lastKey = \array_pop($path);
        $json = &$this->items;

        foreach ($path as $subKey) {
            if (!\array_key_exists($subKey, $json)) {
                return null;
            }

            $json = &$json[$subKey];
        }

        $value = $json[$lastKey];

        if (\is_array($json) && !($json instanceof static)) {
            $value = new static($value);

            $json[$lastKey] = $value;
        }

        return $value;
    }

    /**
     * Set the item at a given offset.
     *
     * @param  mixed $key
     * @param  mixed $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $path = $this->resolveOffset($key);
        $lastKey = \array_pop($path);
        $json = &$this->items;

        foreach ($path as $subKey) {
            if (!\array_key_exists($subKey, $json)) {
                $json[$subKey] = [];
            }

            $json = &$json[$subKey];
        }

        if (\is_null($lastKey) || empty($lastKey)) {
            $json[] = $value;
        } else {
            $json[$lastKey] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  mixed $key
     * @return void
     */
    public function offsetUnset($key)
    {
        $path = $this->resolveOffset($key);
        $lastKey = \array_pop($path);
        $json = $this->items;

        foreach ($path as $subKey) {
            if (!\array_key_exists($subKey, $json)) {
                $json[$subKey] = [];
            }

            $json = $json[$subKey];
        }

        unset($json[$lastKey]);
    }

    /**
     * Check if a key exists.
     *
     * @param mixed $key
     * @return boolean
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Get a  a key exists.
     *
     * @param mixed $key
     * @return boolean
     */
    public function __get($key)
    {
        return $this->offsetGet($key);
    }

    /**
     * Set the item at a given offset.
     *
     * @param  mixed $key
     * @param  mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  mixed $key
     * @return void
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }
}
