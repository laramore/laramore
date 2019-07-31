<?php
/**
 * Add a property management.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Traits;

use Illuminate\Support\Str;

trait HasProperties
{
    /**
     * Indicate if a property exists.
     *
     * @param  string $key
     * @return boolean
     */
    public function hasProperty(string $key): bool
    {
        return isset($this->$key);
    }

    /**
     * Return a property by its name.
     *
     * @param  string $key
     * @return mixed
     * @throws \ErrorException If no property exists with this name
     */
    public function getProperty(string $key)
    {
        if ($this->hasProperty($key)) {
            if (\method_exists($this, $method = 'get'.\ucfirst($key))) {
                return \call_user_func([$this, $method]);
            }

            return $this->$key;
        }

        throw new \ErrorException("The property $key does not exist");
    }

    /**
     * Define a property.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return self
     */
    protected function defineProperty(string $key, $value)
    {
        $this->$key = $value;

        return $this;
    }

    /**
     * Manage the definition of a property.
     *
     * @param string $key
     * @param mixed  $value
     * @return self
     * @throws \ErrorException If no property exists with this name
     */
    public function setProperty(string $key, $value)
    {
        if (\method_exists($this, $key)) {
            \call_user_func([$this, $key], $value);
        } else if (\property_exists($this, $key)) {
            $this->defineProperty($key, $value);
        } else if (\defined($const = 'static::'.strtoupper(Str::snake($key)))) {
            if ($value) {
                $this->addRule(constant($const));
            } else {
                $this->removeRule(constant($const));
            }
        } else {
            throw new \ErrorException("The property $key cannot be set as it does not exist");
        }

        return $this;
    }

    /**
     * Return the value of a property if it exists with a given name.
     *
     * @param  string $key
     * @return mixed
     * @throws \ErrorException If no property exists with this name
     */
    public function __get(string $key)
    {
        return $this->getProperty($key);
    }

    /**
     * Set the value of a property.
     *
     * @param  string $key
     * @param mixed  $value
     * @throws \ErrorException If no property exists with this name
     * @return mixed
     */
    public function __set(string $key, $value)
    {
        return $this->setProperty($key, $value);
    }

    /**
     * Indicate if a property exists.
     *
     * @param  string $key
     * @return boolean
     */
    public function __isset(string $key): bool
    {
        return $this->hasProperty($key);
    }

    /**
     * Return a property, or set one.
     *
     * @param  string $method
     * @param  array  $arg
     * @return mixed
     */
    public function __call(string $method, array $args)
    {

        if (count($args) === 0) {
            if (Str::startsWith($method, 'get')) {
                return $this->getProperty(Str::camel(substr(Str::snake($method), 4)));
            } else {
                return $this->setProperty($method, true);
            }
        } else if (count($args) === 1) {
            $this->setProperty($method, $args[0]);
        } else {
            $this->setProperty($method, $args);
        }

        return $this;
    }
}
