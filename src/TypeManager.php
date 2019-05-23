<?php
/**
 * Define a field types used by Laramore.
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

    protected $types = [];
    protected $valueNames = [];

    public function __construct(array $defaultTypes)
    {
        foreach ($defaultTypes as $name) {
            $this->types[$name] = new Type($name);
        }
    }

    public function __call(string $method, array $args)
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

    public function hasType(string $name)
    {
        return isset($this->types[$name]);
    }

    public function getType(string $name)
    {
        return $this->types[$name];
    }

    public function setType(string $name)
    {
        $this->checkLock();

        $this->types[$name] = $type = new Type($name);

        foreach ($this->valueNames as $valueName) {
            $type->setValue($valueName, $name);
        }

        return $type;
    }

    public function addTypeValue(string $name)
    {
        $this->checkLock();

        if (!in_array($name, $this->valueNames)) {
            $this->valueNames[] = $name;

            foreach ($this->getTypes() as $type) {
                if (!$type->hasValue($name)) {
                    $type->setValue($name, $type->getName());
                }
            }
        }
    }

    public function getTypes()
    {
        return $this->types;
    }

    protected function locking()
    {
        foreach ($this->getTypes() as $type) {
            $type->lock();
        }
    }
}
