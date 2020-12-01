<?php
/**
 * Define a boolean field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

use Illuminate\Support\Arr;
use Laramore\Elements\{
    EnumElement, EnumManager
};
use Laramore\Exceptions\LockException;

class Enum extends BaseAttribute
{
    /**
     * All listed elements
     *
     * @var EnumManager
     */
    protected $elements;

    /**
     * Enum manager class.
     *
     * @var string
     */
    protected $enumManagerClass = EnumManager::class;

    /**
     * Define all proxies for this field.
     *
     * @return void
     */
    protected function setProxies()
    {
        parent::setProxies();

        $class = Arr::get($this->elementsProxy, 'class', Arr::get($this->proxy, 'class', config('field.proxy.class')));
        $proxies = Arr::get($this->elementsProxy, 'configurations', []);

        $proxyHandler = $this->getMeta()->getProxyHandler();
        $elements = $this->getElements()->all();

        foreach ($proxies as $methodName => $data) {
            if (\is_null($data)) {
                continue;
            }

            foreach ($elements as $element) {
                $proxyHandler->add(new $class(
                    $this, $element, $methodName,
                    Arr::get($data, 'static', false), Arr::get($data, 'needs_value', false),
                    Arr::get($data, 'templates.name'), Arr::get($data, 'templates.multi_name')
                ));
            }
        }
    }

    /**
     * Define all elements for this enum field.
     *
     * @param array<string>|array<EnumElement>|EnumManager $elements
     * @return self
     */
    public function elements($elements)
    {
        $this->checkNeedsToBeLocked(false);

        $managerClass = $this->enumManagerClass;

        if ($elements instanceof $managerClass) {
            $this->defineProperty('elements', $elements);
        } else if (\is_array($elements)) {
            $this->defineProperty('elements', new $managerClass($elements));
        }

        return $this;
    }

    /**
     * Return the element manager for this field.
     *
     * @return EnumManager
     */
    public function getElements(): EnumManager
    {
        return $this->elements;
    }

    /**
     * Return elements.
     *
     * @return array<EnumElement>
     */
    public function getValues(): array
    {
        return \array_keys($this->elements->all());
    }

    /**
     * Return an element by its name.
     *
     * @param mixed $key
     *
     * @return EnumElement
     */
    public function getElement($key): EnumElement
    {
        return $this->elements->get($key);
    }

    /**
     * Return an element by its value.
     *
     * @param mixed $key
     *
     * @return EnumElement
     */
    public function findElement($key): EnumElement
    {
        return $this->elements->find($key);
    }

    /**
     * Indicate if an element exists.
     *
     * @param mixed $key
     *
     * @return boolean
     */
    public function hasElement($key): bool
    {
        return $this->elements->has($key);
    }

    /**
     * Set the default value.
     *
     * @param mixed $value
     *
     * @return self
     */
    public function default($value=null)
    {
        return parent::default($this->getElement($value));
    }

    /**
     * Get a default value for this field.
     *
     * @return mixed
     */
    public function getDefault()
    {
        $value = $this->default;

        if ($value instanceof EnumElement) {
            return $value;
        }

        return parent::getDefault();
    }

    /**
     * Return the default value.
     *
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->getDefault()->name;
    }

    /**
     * Lock each element.
     *
     * @return void
     */
    protected function locking()
    {
        parent::locking();

        $this->elements->lock();
    }

    /**
     * Check all properties and options before locking the field.
     *
     * @return void
     */
    protected function checkOptions()
    {
        if (!$this->hasProperty('elements') || $this->elements->count() === 0) {
            throw new LockException("Need a list of elements for `{$this->getName()}`", 'elements');
        }
    }

    /**
     * Dry the value in a simple format.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function dry($value)
    {
        if (\is_null($value)) {
            return $value;
        }

        return $value->name;
    }

    /**
     * Hydrate the value in a simple format.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function hydrate($value)
    {
        if (\is_null($value) || ($value instanceof EnumElement)) {
            return $value;
        }
        
        return $this->getElement($value);
    }

    /**
     * Cast the value in the correct format.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function cast($value)
    {
        if (\is_null($value) || ($value instanceof EnumElement)) {
            return $value;
        }

        return $this->getElement($value);
    }

    /**
     * Serialize the value for outputs.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function serialize($value)
    {
        return $value->native;
    }

    /**
     * Return if the value is the right element as expected or not.
     *
     * @param  EnumElement $value
     * @param  mixed       $element
     * @param  boolean     $expected
     * @return boolean
     */
    public function is(EnumElement $value, $element, bool $expected=true): bool
    {
        return ($value === $this->cast($element)) === $expected;
    }

    /**
     * Return if the value is not the right element.
     *
     * @param  EnumElement $value
     * @param  mixed       $element
     * @return boolean
     */
    public function isNot(EnumElement $value, $element): bool
    {
        return $this->is($value, $element, false);
    }
}
