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
     * Properties added form config.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Indicate if a property exists.
     *
     * @param  string $key
     * @return boolean
     */
    public function hasProperty(string $key): bool
    {
        if (\method_exists($this, $method = 'has'.\ucfirst($key))) {
            return \call_user_func([$this, $method]);
        }

        return \property_exists($this, $key) && isset($this->$key);
    }

    /**
     * Return a property by its name.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function getProperty(string $key, $default=null)
    {
        if ($this->hasProperty($key)) {
            if (\method_exists($this, $method = 'get'.\ucfirst($key))) {
                return \call_user_func([$this, $method]);
            }

            return $this->$key;
        } else if (\array_key_exists($this->config, $key)) {
            return $this->config[$key];
        }

        return $default;
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
     * @throws \ErrorException If no property exists with this name.
     */
    public function setProperty(string $key, $value)
    {
        if (\method_exists($this, $key)) {
            \call_user_func([$this, $key], $value);
        } else if (\property_exists($this, $key)) {
            $this->defineProperty($key, $value);
        } else {
            // Try with plural key.
            $pluralKey = Str::plural($key);

            if (\method_exists($this, $pluralKey)) {
                \call_user_func([$this, $pluralKey], [$value]);
            } else if (\property_exists($this, $pluralKey)) {
                $this->defineProperty($pluralKey, [$value]);
            } else {
                throw new \ErrorException("The property `$key` cannot be set as it does not exist");
            }
        }

        return $this;
    }

    /**
     * Manage the definition of many properties.
     *
     * @param string $key
     * @param mixed  $value
     * @return self
     * @throws \ErrorException If no property exists with this name.
     */
    public function setConfig(string $key, $value)
    {
        if (!isset($this->config[$key])) {
            throw new \Exception("The config key `$key` does not exist");
        }

        $this->config[$key] = $value;

        return $this;
    }

    /**
     * Init all properties with a set of properties.
     * @param array $properties
     * @return self
     */
    protected function initProperties(array $properties)
    {
        foreach ($properties as $key => $value) {
            $key = Str::camel($key);

            if (\property_exists($this, $key)) {
                $this->setProperty($key, $value);
            } else {
                $this->config[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * Manage the definition of many properties.
     *
     * @param array $properties
     * @return self
     * @throws \ErrorException If no property exists with this name.
     */
    public function setProperties(array $properties)
    {
        foreach ($properties as $key => $value) {
            $this->setProperty($key, $value);
        }

        return $this;
    }

    /**
     * Return the value of a property if it exists with a given name.
     *
     * @param  string $key
     * @return mixed
     * @throws \ErrorException If no property exists with this name.
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
     * @throws \ErrorException If no property exists with this name.
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
     * @param  array  $args
     * @return mixed
     */
    public function __call(string $method, array $args)
    {
        if (\count($args) === 0) {
            if (Str::startsWith($method, 'get')) {
                return $this->getProperty(Str::camel(substr(Str::snake($method), 4)));
            } else if (Str::startsWith($method, 'has')) {
                return $this->hasProperty(Str::camel(substr(Str::snake($method), 4)));
            } else if (Str::startsWith($method, 'set')) {
                return $this->setProperty(Str::camel(\substr(Str::snake($method), 4)), $args);
            }

            return $this->setProperty($method, true);
        } else {
            $args = (\count($args) === 1) ? $args[0] : $args;

            if (Str::startsWith($method, 'set')) {
                return $this->setProperty(Str::camel(\substr(Str::snake($method), 4)), $args);
            }

            $this->setProperty($method, $args);
        }

        return $this;
    }
}
