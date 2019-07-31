<?php
/**
 * Define a field type manager used by Laramore.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore;

use Laramore\Traits\IsLocked;

class TypeManager
{
    use IsLocked;

    /**
     * All existing types.
     *
     * @var array
     */
    protected $types = [];

    /**
     * All type value names.
     * Examples: migration, factory, admin
     *
     * @var array
     */
    protected $valueNames = [];

    /**
     * Build default types managed by this manager.
     *
     * @param array $defaultTypes
     */
    public function __construct(array $defaultTypes=[])
    {
        foreach ($defaultTypes as $name) {
            $this->types[$name] = new Type($name);
        }
    }

    /**
     * Indicate if type exists with the given name.
     *
     * @param  string $name
     * @return boolean
     */
    public function hasType(string $name): bool
    {
        return \array_key_exists($name, $this->types);
    }

    /**
     * Returns the type with the given name.
     *
     * @param  string $name
     * @return Type
     * @throws \ErrorException If no type exists with this name.
     */
    public function getType(string $name): Type
    {
        if ($this->hasType($name)) {
            return $this->types[$name];
        }

        throw new \ErrorException("The type $name does not exist");
    }

    /**
     * Create a new type with a specific name.
     * Override is allowed, be carefull.
     *
     * @param string $name
     * @return Type
     */
    public function createType(string $name): Type
    {
        $this->needsToBeUnlocked();

        $type = new Type($name);
        $this->setType($type);

        return $type;
    }

    /**
     * Return the type or create one with the given name.
     *
     * @param  string $name
     * @return Type
     */
    public function getOrCreateType(string $name): Type
    {
        if ($this->hasType($name)) {
            return $this->getType($name);
        } else {
            return $this->createType($name);
        }
    }

    /**
     * Define a type with its name.
     * Override is allowed, be carefull.
     *
     * @param  Type $type
     * @return self
     */
    public function setType(Type $type)
    {
        $this->types[$name = $type->getName()] = $type;

        foreach ($this->valueNames as $valueName) {
            if (!$type->hasValue($valueName)) {
                $type->setValue($valueName, $name);
            }
        }

        return $this;
    }

    /**
     * Return all possible types.
     *
     * @return array
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * Indicate if value name is defined.
     *
     * @param  string $name
     * @return boolean
     */
    public function hasValueName(string $name): bool
    {
        return \in_array($name, $this->valueNames);
    }

    /**
     * Add a value name and set the value for this name on each type.
     *
     * @param string $name
     * @return void
     */
    public function addValueName(string $name)
    {
        $this->needsToBeUnlocked();

        if (!$this->hasValueName($name)) {
            $this->valueNames[] = $name;

            foreach ($this->getTypes() as $type) {
                if (!$type->hasValue($name)) {
                    $type->setValue($name, $type->getName());
                }
            }
        }
    }

    /**
     * Return the list of value names.
     *
     * @return array
     */
    public function getValueNames(): array
    {
        return $this->valueNames;
    }

    /**
     * Lock every type.
     *
     * @return void
     */
    protected function locking()
    {
        foreach ($this->getTypes() as $type) {
            $type->lock();
        }
    }

    /**
     * Handle all method calls.
     * Returns the type with given method name.
     *
     * @param  string $method Type name.
     * @param  array  $args   The first argument could be the value name of the type.
     * @return Type
     */
    public function __call(string $method, array $args): Type
    {
        if (!$this->hasType($method)) {
            $this->createType($method);
        }

        $type = $this->getType($method);

        if (\count($args) === 0) {
            return $type;
        } else {
            return $type->{$args[0]};
        }
    }
}
