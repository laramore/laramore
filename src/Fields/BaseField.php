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
use Illuminate\Database\Eloquent\Model;
use Laramore\Interfaces\IsAField;
use Laramore\Traits\{
    IsOwnedAndLocked, HasProperties
};
use Laramore\Proxies\FieldProxy;
use Laramore\Meta;
use Laramore\Exceptions\FieldValidationException;
use Laramore\Validations\ValidationErrorBag;
use Closure;

abstract class BaseField implements IsAField
{
    use IsOwnedAndLocked, HasProperties {
        setProperty as protected forceProperty;
    }

    protected $nullable;

    /**
     * Return a property by its name.
     *
     * @param  string $key
     * @return mixed
     * @throws \ErrorException If no property exists with this name.
     */
    public function getProperty(string $key, bool $fail=true)
    {
        if ($this->hasProperty($key)) {
            if (\method_exists($this, $method = 'get'.\ucfirst($key))) {
                return \call_user_func([$this, $method]);
            }

            return $this->$key;
        } else if (\defined($const = 'static::'.\strtoupper(Str::snake($key)))) {
            return $this->hasRule(\constant($const));
        }

        if ($fail) {
            throw new \ErrorException("The property $key does not exist");
        }
    }

    /**
     * Manage the definition of a property.
     *
     * @param string $key
     * @param mixed  $value
     * @return self
     * @throws \ErrorException If no property exists with this name.
     */
    public function setProperty(string $key, $value)
    {
        $this->needsToBeUnlocked();

        return $this->forceProperty($key, $value);
    }

    /**
     * Define the name of the field.
     *
     * @param  string $name
     * @return self
     */
    public function name(string $name)
    {
        $this->needsToBeUnlocked();

        if (!is_null($this->name)) {
            throw new \LogicException('The field name cannot be defined multiple times');
        }

        $this->name = $name;

        return $this;
    }

    protected function locking()
    {
        $this->setValidations();
        $this->setProxies();
    }

    abstract protected function setValidations();

    protected function setProxies()
    {
        $this->setProxy('getErrors', [], ['model'], $this->generateProxyMethodName('get', 'errors'));
        $this->setProxy('isValid', [], ['model'], $this->generateProxyMethodName('is', 'valid'));
        $this->setProxy('where', ['instance'], ['builder']);
    }

    /**
     * Return the meta of this field.
     * The owner could be a composite field and so on but not the coresponded meta.
     *
     * @return Meta
     */
    public function getMeta(): Meta
    {
        $owner = $this->getOwner();

        while (!($owner instanceof Meta)) {
            $owner = $owner->getOwner();
        }

        return $owner;
    }

    protected function setValidation(string $validationClass, int $property=null)
    {
        $handler = $this->getMeta()->getValidationHandler();

        if ($handler->has($this->name, $name = $validationClass::getStaticName())) {
            $validation = $handler->get($this->name, $name);
        } else {
            if (is_null($property)) {
                $validation = new $validationClass($this);
            } else {
                $validation = new $validationClass($this, $property);
            }

            $handler->add($validation);
        }

        return $validation;
    }

    protected function setProxy(string $methodName, array $injections=[], array $on=['model'], string $proxyName=null)
    {
        $proxy = new FieldProxy(($proxyName ?? $this->generateProxyMethodName($methodName)), $this, $methodName, $injections, $on);

        $this->getMeta()->getProxyHandler()->add($proxy);

        return $proxy;
    }

    protected function generateProxyMethodName(string $firstPart, string $secondPart='')
    {
        return $firstPart.\ucfirst(Str::camel($this->attname)).\ucfirst($secondPart);
    }

    public function getErrors($value): ValidationErrorBag
    {
        return $this->getMeta()->getValidationHandler()->getValidationErrors($this, $value);
    }

    public function isValid($value): bool
    {
        return $this->getOwner()->getErrorsFieldAttribute($this, $value)->count() === 0;
    }

    public function check($value)
    {
        $errors = $this->getOwner()->getErrorsFieldAttribute($this, $value);

        if ($errors->count()) {
            throw new FieldValidationException($this, $errors);
        }
    }
}
