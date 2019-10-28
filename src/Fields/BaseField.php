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
    IsOwned, IsLocked, HasProperties
};
use Laramore\Traits\Field\HasRules;
use Laramore\Proxies\FieldProxy;
use Laramore\Meta;
use Laramore\Exceptions\FieldValidationException;
use Laramore\Validations\{
    NotNullable, ValidationErrorBag
};
use Closure;

abstract class BaseField implements IsAField
{
    use IsOwned, IsLocked, HasProperties, HasRules {
        setOwner as protected setOwnerFromTrait;
        setProperty as protected forceProperty;
    }

    protected $meta;
    protected $rules;

    protected $default;
    protected $unique;

    /**
     * Set of rules.
     * Common to all fields.
     *
     * @var integer
     */

    // Indicate that no rules are applied.
    public const NONE = 0;

    // Indicate that the field accepts nullable values.
    public const NULLABLE = 1;

    // Except if trying to set a nullable value.
    public const NOT_NULLABLE = 2;

    // Indicate if it is visible by default.
    public const VISIBLE = 4;

    // Indicate if it is fillable by default.
    public const FILLABLE = 8;

    // Indicate if it is required by default.
    public const REQUIRED = 16;

    // Default rules for any type of field.
    public const DEFAULT_FIELD = (self::VISIBLE | self::FILLABLE | self::REQUIRED);

    protected static $defaultRules = self::DEFAULT_FIELD;

    /**
     * Create a new field with basic rules.
     * The constructor is protected so the field is created writing left to right.
     * ex: Text::field()->maxLength(255) insteadof (new Text)->maxLength(255).
     *
     * @param integer|string|array $rules
     */
    protected function __construct($rules=null)
    {
        $this->addRules($rules ?: static::$defaultRules);
    }

    /**
     * Call the constructor and generate the field.
     *
     * @param  array|integer|null $rules
     * @return static
     */
    public static function field($rules=null)
    {
        return new static($rules);
    }

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

        if (\defined($const = 'static::'.\strtoupper(Str::snake($key)))) {
            if ($key === false) {
                return $this->removeRule(\constant($const));
            } else {
                return $this->addRule(\constant($const));
            }
        }

        return $this->forceProperty($key, $value);
    }

    /**
     * Define the name of the field.
     *
     * @param  string $name
     * @return self
     */
    protected function setName(string $name)
    {
        $this->needsToBeUnlocked();

        if (!is_null($this->name)) {
            throw new \LogicException('The field name cannot be defined multiple times');
        }

        $this->name = $name;

        return $this;
    }

    /**
     * Define the field as not visible.
     *
     * @param  boolean $hidden
     * @return self
     */
    public function hidden(bool $hidden=true)
    {
        return $this->visible(!$hidden);
    }

    /**
     * Define a default value for this field.
     *
     * @param  mixed $value
     * @return self
     */
    public function default($value=null)
    {
        $this->needsToBeUnlocked();

        $this->removeRule(static::REQUIRED);

        if (\is_null($value)) {
            $this->nullable();
        }

        $this->defineProperty('default', $value);

        return $this;
    }

    protected function setOwner($owner)
    {
        $this->setOwnerFromTrait($owner);

        if (!$this->hasProperty('meta')) {
            while (!($owner instanceof Meta)) {
                $owner = $owner->getOwner();
            }

            $this->setMeta($owner);
        }
    }

    protected function owned()
    {
        $owner = $this->getOwner();

        if (!($owner instanceof Meta) && !($owner instanceof CompositeField)) {
            throw new \LogicException('A field should be owned by a Meta or a CompositeField');
        }
    }

    protected function locking()
    {
        $this->checkRules();
        $this->setValidations();
        $this->setProxies();
    }

    /**
     * Check all properties and rules before locking the field.
     *
     * @return void
     */
    protected function checkRules()
    {
        if ($this->hasProperty('default')) {
            if (\is_null($this->default)) {
                if ($this->hasRule(self::NOT_NULLABLE)) {
                    throw new \LogicException("This field cannot be null and defined as null by default");
                } else if (!$this->hasRule(self::NULLABLE) && !$this->hasRule(self::REQUIRED)) {
                    throw new \LogicException("This field cannot be null, defined as null by default and not required");
                }
            } else if ($this->hasRule(self::REQUIRED)) {
                throw new \LogicException("This field cannot have a default value and be required");
            }
        }

        if ($this->hasRule(self::NOT_NULLABLE)) {
            if ($this->hasRule(self::NULLABLE)) {
                throw new \LogicException("This field cannot be nullable and not nullable or strict on the same time");
            }
        }
    }

    protected function setValidations()
    {
        if ($this->hasRule(self::NOT_NULLABLE)) {
            $this->setValidation(NotNullable::class);
        }
    }

    protected function setProxies()
    {
        $this->setProxy('getErrors', [], ['model'], $this->generateProxyMethodName('get', 'errors'));
        $this->setProxy('isValid', [], ['model'], $this->generateProxyMethodName('is', 'valid'));
        $this->setProxy('relate', ['instance'], ['model', 'builder'], Str::camel($this->name));
        $this->setProxy('where', ['instance'], ['builder']);
        $this->setProxy('whereNull', ['instance'], ['builder'], $this->generateProxyMethodName('doesntHave'));
        $this->setProxy('whereNotNull', ['instance'], ['builder'], $this->generateProxyMethodName('has'));
    }

    /**
     * Define the name of the field.
     *
     * @param  string $name
     * @return self
     */
    public function setMeta(Meta $meta)
    {
        $this->needsToBeUnlocked();

        if ($this->hasProperty('meta')) {
            throw new \LogicException('The meta cannot be defined multiple times');
        }

        $this->defineProperty('meta', $meta);

        return $this;
    }

    /**
     * Return the meta of this field.
     * The owner could be a composite field and so on but not the coresponded meta.
     *
     * @return Meta
     */
    public function getMeta(): Meta
    {
        return $this->meta;
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
        return $firstPart.\ucfirst(Str::camel($this->name)).\ucfirst($secondPart);
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
