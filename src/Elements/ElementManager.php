<?php
/**
 * Define a element manager used by Laramore.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Elements;

use Illuminate\Support\{
    Arr, Str
};
use Laramore\Traits\IsLocked;

class ElementManager
{
    use IsLocked;

    /**
     * The element to manage.
     *
     * @var string
     */
    protected $elementClass = Element::class;

    /**
     * All existing elements.
     *
     * @var array<Element>
     */
    protected $elements = [];

    /**
     * All element value names.
     * Examples: migration, factory, admin (for types)
     *
     * @var array<string>
     */
    protected $definitions = [];

    /**
     * Build default elements managed by this manager.
     *
     * @param array<Element> $defaults
     */
    public function __construct(array $defaults=[])
    {
        $this->set($defaults);
    }

    /**
     * Indicate if an element exists with the given name.
     *
     * @param  string $name
     * @return boolean
     */
    public function has(string $name): bool
    {
        return \array_key_exists($name, $this->elements);
    }

    /**
     * Return the first existant element with the given native value.
     *
     * @param  string $native
     * @return Element
     * @throws \ErrorException If no element exists with this native value.
     */
    public function find(string $native): Element
    {
        foreach ($this->all() as $element) {
            if ($element->native === $native) {
                return $element;
            }
        }

        throw new \ErrorException("No element `{$this->elementClass}` have `$native` as native value");
    }

    /**
     * Returns the element with the given name.
     *
     * @param  string $name
     * @return Element
     * @throws \ErrorException If no element exists with this name.
     */
    public function get(string $name): Element
    {
        if ($this->has($name)) {
            return $this->elements[$name];
        }

        throw new \ErrorException("The element `{$this->elementClass}` with the name `$name` does not exist");
    }

    /**
     * Create a new element with a specific name.
     * Override is allowed, be carefull.
     *
     * @param string $name
     * @param mixed  $native
     * @return Element
     */
    public function create(string $name, $native=null): Element
    {
        $this->needsToBeUnlocked();

        $element = new $this->elementClass($name, $native ?: $name);
        $this->set($element);

        return $element;
    }

    /**
     * Define an element with its name.
     * Override is allowed, be carefull.
     *
     * @param  Element|array<Element> $element
     * @return self
     */
    public function set($element): self
    {
        if ($element instanceof $this->elementClass) {
            $this->elements[$element->getName()] = $element;
        } else if (\is_array($element)) {
            if (Arr::isAssoc($element)) {
                foreach ($element as $key => $value) {
                    $element = $this->create($key);

                    if (\is_array($value)) {
                        foreach ($value as $keyValue => $elementValue) {
                            $element->set($keyValue, $elementValue);
                        }
                    } else {
                        $element->native = $value;
                    }
                }
            } else {
                foreach ($element as $subElement) {
                    if (\is_string($subElement)) {
                        $this->create($subElement);
                    } else {
                        $this->set($subElement);
                    }
                }
            }
        } else {
            $class = static::class;

            throw new \ErrorException("The manager `$class` can set only arrays or the element `{$this->elementClass}`");
        }

        return $this;
    }

    /**
     * Return all possible elements.
     *
     * @return array<Element>
     */
    public function all(): array
    {
        return $this->elements;
    }

    /**
     * Count the number of elements.
     *
     * @return integer
     */
    public function count(): int
    {
        return \count($this->elements);
    }

    /**
     * Indicate if a value name is defined.
     *
     * @param  string $name
     * @return boolean
     */
    public function doesDefine(string $name): bool
    {
        return \array_key_exists($name, $this->definitions);
    }

    /**
     * Add a value name and set the value for this name on each type.
     *
     * @param string $name
     * @param mixed  $default
     * @return self
     */
    public function define(string $name, $default=null)
    {
        $this->needsToBeUnlocked();

        if (!$this->doesDefine($name)) {
            $this->definitions[$name] = $default;

            foreach ($this->all() as $element) {
                if (!$element->has($name)) {
                    $element->set($name, ($default ?? $element->getName()));
                }
            }
        }

        return $this;
    }

    /**
     * Return the list of value names.
     *
     * @return array<string>
     */
    public function definitions(): array
    {
        return $this->definitions;
    }

    /**
     * Lock every element after defining all definitions.
     *
     * @return void
     */
    protected function locking()
    {
        foreach ($this->all() as $name => $element) {
            foreach ($this->definitions as $keyName => $valueName) {
                if (!$element->has($keyName)) {
                    $element->set($keyName, ($valueName ?? $name));
                }
            }

            $element->lock();
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
     * Handle all method calls.
     * Returns the element with the given method name.
     *
     * @param  string $method Element name.
     * @param  array  $args   The first argument could be a value name of the element.
     * @return mixed
     */
    public function __call(string $method, array $args)
    {
        $method = Str::snake($method);

        if (\count($args)) {
            return $this->get($method)->__invoke(...$args);
        } else {
            return $this->get($method);
        }
    }
}
