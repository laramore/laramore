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
use Illuminate\Database\Eloquent\Model;
use Laramore\Interfaces\IsAField;
use Laramore\Traits\{
    IsOwnedAndLocked, HasProperties
};
use Laramore\Meta;
use Laramore\Exceptions\FieldValidationException;
use Laramore\Validations\ValidationErrorBag;

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
    public function getProperty(string $key)
    {
        if ($this->hasProperty($key)) {
            if (\method_exists($this, $method = 'get'.\ucfirst($key))) {
                return \call_user_func([$this, $method]);
            }

            return $this->$key;
        } else if (\defined($const = 'static::'.\strtoupper(Str::snake($key)))) {
            return $this->hasRule(\constant($const));
        }

        throw new \ErrorException("The property $key does not exist");
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
        $this->checkRules();
        $this->setValidations();
    }

    abstract protected function checkRules();

    abstract protected function setValidations();

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

    protected function setValidation(string $validationClass)
    {
        $handler = $this->getMeta()->getValidationHandler();

        if ($handler->hasValidation($this->name, $name = $validationClass::getStaticName())) {
            $validation = $handler->getValidation($this->name, $name);
        } else {
            $validation = new $validationClass($this->getName());

            $handler->addValidation($validation);
        }

        return $validation;
    }

    public function getValidationErrorsForValue(Model $model, $value): ValidationErrorBag
    {
        return $this->getMeta()->getValidationHandler()->getValidationErrors($this->name, $model, $value);
    }

    public function isAValidValue(Model $model, $value): bool
    {
        return $this->getValidationErrorsForValue($model, $value)->count() === 0;
    }

    public function checkValue(Model $model, $value): void
    {
        $errors = $this->getValidationErrorsForValue($model, $value);

        if ($errors->count()) {
            throw new FieldValidationException($this, $errors);
        }
    }
}
