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
        return array_key_exists($name, $this->types);
    }

    /**
     * Returns the type with the given name.
     *
     * @param  string $name
     * @return Type
     */
    public function getType(string $name): Type
    {
        if ($this->hasType($name)) {
            return $this->types[$name];
        } else {
            throw new \Exception('Th');
        }
    }

    public function setType(string $name)
    {
        $this->needsToBeUnlocked();

        $this->types[$name] = $type = new Type($name);

        foreach ($this->valueNames as $valueName) {
            $type->setValue($valueName, $name);
        }

        return $type;
    }

    public function getTypes()
    {
        return $this->types;
    }

    public function hasValueName(string $name)
    {
        return in_array($name, $this->valueNames);
    }

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

    public function getValueNames()
    {
        return $this->valueNames;
    }

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
            $this->setType($method);
        }

        $type = $this->getType($method);

        if (count($args) === 0) {
            return $type;
        } else {
            return $type->{$args[0]};
        }
    }
}
