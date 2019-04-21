<?php
/**
 * Define all basic field methods.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

use Illuminate\Support\Str;
use Laramore\Interfaces\IsAField;
use Laramore\Traits\IsOwnedAndLocked;

abstract class BaseField implements IsAField
{
    use IsOwnedAndLocked;

    protected static $readOnlyProperties = [
        'type'
    ];
    protected $properties = [];

    public function __call(string $method, array $args)
    {
        $this->checkLock();

        if (count($args) === 0) {
            $this->setProperty($method, true);
        } else if (count($args) === 1) {
            $this->setProperty($method, $args[0]);
        } else {
            $this->setProperty($method, $args);
        }

        return $this;
    }

    public function __get(string $key)
    {
        return $this->getProperty($key);
    }

    public function __set(string $key, $value)
    {
        return $this->setProperty($key, $value);
    }

    public function __isset(string $key): bool
    {
        return $this->hasProperty($key);
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function hasProperty(string $key): bool
    {
        return isset($this->properties[$key]);
    }

    public function getProperty(string $key)
    {
        if (property_exists($this, $key)) {
            return $this->$key;
        } else if ($this->hasProperty($key)) {
            return $this->properties[$key];
        } else if (defined($const = 'self::'.strtoupper(Str::snake($key)))) {
            return $this->hasRule(constant($const));
        }

        return null;
    }

    public function setProperty(string $key, $value)
    {
        $this->checkLock();

        if (in_array($key, static::$readOnlyProperties)) {
            throw new \Exception("The propery $key cannot be set");
        } else if (method_exists($this, $key)) {
            $this->$key($value);
        } else {
            $this->properties[$key] = $value;
        }

        return $this;
    }

    /**
     * Define the name of the field.
     *
     * @param  string $name
     * @return static
     */
    public function name(string $name)
    {
        $this->checkLock();

        if ($this->hasProperty('name')) {
            throw new \Exception('The field name cannot be defined multiple times');
        }

        $this->properties['name'] = $name;
    }

    /**
     * Method called during the locking.
     * Has to be protected or private.
     *
     * @return void
     */
    abstract protected function locking();
}
