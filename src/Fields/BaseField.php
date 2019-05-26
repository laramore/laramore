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
use Laramore\Observers\ModelObserver;
use Laramore\Interfaces\IsAField;
use Laramore\Traits\IsOwnedAndLocked;
use Laramore\Meta;

abstract class BaseField implements IsAField
{
    use IsOwnedAndLocked;

    protected $name;
    protected $nullable;

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

    public function hasProperty(string $key): bool
    {
        return isset($this->$key);
    }

    public function getProperty(string $key)
    {
        if (property_exists($this, $key)) {
            if (method_exists($this, $method = 'get'.ucfirst($key))) {
                return $this->$method();
            }

            return $this->$key;
        } else if (defined($const = 'static::'.strtoupper(Str::snake($key)))) {
            return $this->hasRule(constant($const));
        }

        return null;
    }

    protected function defineProperty(string $key, $value)
    {
        $this->$key = $value;

        return $this;
    }

    public function setProperty(string $key, $value)
    {
        $this->checkLock();

        if (method_exists($this, $key)) {
            call_user_func([$this, $key], $value);
        } else if (property_exists($this, $key)) {
            $this->defineProperty($key, $value);
        } else if (defined($const = 'static::'.strtoupper(Str::snake($key)))) {
            if ($value) {
                $this->addRule(constant($const));
            } else {
                $this->removeRule(constant($const));
            }
        } else {
            throw new \Exception("The propery $key cannot be set");
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

        if (!is_null($this->name)) {
            throw new \Exception('The field name cannot be defined multiple times');
        }

        $this->name = $name;
    }

    public function getMeta()
    {
        do {
            $owner = $this->getOwner();
        } while (!($owner instanceof Meta));

        return $owner;
    }

    public function getModelClass()
    {
        return $this->getMeta()->getModelClass;
    }

    protected function addObserver(ModelObserver $observer)
    {
        $this->getMeta()->getModelObservableHandler()->addObserver($observer);

        return $this;
    }

    /**
     * Method called during the locking.
     * Has to be protected or private.
     *
     * @return void
     */
    abstract protected function locking();
}
