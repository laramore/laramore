<?php
/**
 * Define a specific element.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Elements;

use Illuminate\Support\Str;
use Laramore\Exceptions\LockException;
use Laramore\Traits\IsLocked;

abstract class BaseElement
{
    use IsLocked;

    /**
     * The element name.
     *
     * @var string
     */
    protected $name;

    /**
     * All defined values for this element.
     *
     * @var array
     */
    protected $values = [];

    /**
     * Create the element with a specific name and a native element value.
     *
     * @param string $name
     * @param string $native
     */
    public function __construct(string $name, string $native)
    {
        $this->name = $name;
        $this->set('native', $native);
    }

    /**
     * Return the element name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Indicate if the element has a value for a given name.
     *
     * @param  string $key
     * @return boolean
     */
    public function has(string $key): bool
    {
        return \in_array($key, \array_keys($this->values));
    }

    /**
     * Return the element value for a given name.
     *
     * @param  string $key
     * @return mixed
     * @throws \ErrorException If this type has no value for a specific key name.
     */
    public function get(string $key='name')
    {
        if ($key === 'name') {
            return $this->name;
        } else if ($this->has($key)) {
            return $this->values[$key];
        }

        $class = static::class;

        throw new \ErrorException("The element $class [{$this->getName()}] has no value for the key $key");
    }

    /**
     * Set the value for a given name.
     *
     * @param string $key
     * @param mixed  $value
     * @return self
     */
    public function set(string $key, $value): self
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
        if (!$this->has('native')) {
            throw new LockException($this, "Need a native element definition for {$this->getName()}", 'native');
        }
    }

    /**
     * Return the value for a given name.
     *
     * @param  string $key
     * @return mixed
     */
    public function __isset(string $key)
    {
        return $this->has($key);
    }

    /**
     * Return the value for a given name.
     *
     * @param  string $key
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->get($key);
    }

    /**
     * Set the value for a given name.
     *
     * @param string $key
     * @param mixed  $value
     * @return self
     */
    public function __set(string $key, $value)
    {
        return $this->set($key, $value);
    }

    /**
     * Return the value for a given name ("get{$name}") or define it.
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
                return $this->get(Str::camel(\substr(Str::snake($method), 4)));
            } else {
                throw new \BadMethodCallException("The method $method does not exist");
            }
        } else {
            return $this->set($method, $args[0]);
        }
    }

    /**
     * Execute the element
     *
     * @param  mixed $valueName
     * @return mixed
     */
    public function __invoke($valueName=null)
    {
        if ($valueName) {
            return $this->get($valueName);
        } else {
            return $this->__toString();
        }
    }

    /**
     * Return the element native value.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->get('native');
    }
}
