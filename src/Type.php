<?php
/**
 * Define a specific field.
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

    protected $name;
    protected $values = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function __call(string $method, array $args)
    {
        if (count($args) === 0) {
            if (Str::startsWith($method, 'get')) {
                return $this->getValue(Str::camel(substr(Str::snake($method), 4)));
            } else {
                throw new \Exception('Method does not exist');
            }
        } else {
            return $this->setValue($method, $args[0]);
        }
    }

    public function __get(string $key)
    {
        return $this->getValue($key);
    }

    public function __set(string $key, $value)
    {
        return $this->setValue($key, $value);
    }

    public function hasValue(string $key='value')
    {
        return (property_exists($this, $key) || isset($this->values[$key]));
    }

    public function getValue(string $key='value')
    {
        return ($this->$key ?? $this->values[$key]);
    }

    public function setValue(string $key, $value)
    {
        $this->checkLock();

        $this->values[$key] = $value;

        return $this;
    }

    protected function locking()
    {
    }

    public function __toString()
    {
        return $this->name;
    }
}
