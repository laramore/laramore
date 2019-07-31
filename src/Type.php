<?php
/**
 * Define a specific field type.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore;

use Illuminate\Support\Str;
use Laramore\Traits\IsLocked;

class Type
{
    use IsLocked;

    /**
     * The field name.
     *
     * @var string
     */
    protected $name;

    /**
     * All defined values for this type.
     *
     * @var array
     */
    protected $values = [];

    /**
     * Create the type with a specific name.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Return the name of this type.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Indicate if the type has a value for a given name.
     *
     * @param  string $key
     * @return boolean
     */
    public function hasValue(string $key): bool
    {
        return isset($this->values[$key]);
    }

    /**
     * Return the type value for a given name.
     *
     * @param  string $key
     * @return string
     * @throws \ErrorException If this type has no value for a specific key name.
     */
    public function getValue(string $key='name'): string
    {
        if ($key === 'name') {
            return $this->name;
        } else if ($this->hasValue($key)) {
            return $this->values[$key];
        }

        throw new \ErrorException("The type {$this->getName()} has no value for the key $key");
    }

    /**
     * Set the value for a given name.
     *
     * @param string $key
     * @param string $value
     * @return self
     */
    public function setValue(string $key, string $value)
    {
        $this->needsToBeUnlocked();

        $this->values[$key] = $value;

        return $this;
    }

    /**
     * Actions when locking.
     *
     * @return void
     */
    protected function locking()
    {
        // A type needs only to be locked to avoid changes.
    }

    /**
     * Return the value for a given name.
     *
     * @param  string $key
     * @return string
     */
    public function __get(string $key): string
    {
        return $this->getValue($key);
    }

    /**
     * Set the value for a given name.
     *
     * @param string $key
     * @param string $value
     * @return self
     */
    public function __set(string $key, string $value)
    {
        return $this->setValue($key, $value);
    }

    /**
     * Return the value for a given get{name} or define a value.
     *
     * @param  string $method If start with "get" and 0 args are defined, return the value.
     * @param  array  $args   If one argument is set, define the value for the method name.
     * @return mixed
     * @throws \BadMethodCallException Else.
     */
    public function __call(string $method, array $args)
    {
        if (\count($args) === 0) {
            if (Str::startsWith($method, 'get')) {
                return $this->getValue(Str::camel(\substr(Str::snake($method), 4)));
            } else {
                throw new \BadMethodCallException("The method $method does not exist");
            }
        } else {
            return $this->setValue($method, $args[0]);
        }
    }

    /**
     * Return the main value of this type: its name.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
