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

use Illuminate\Support\{
    Arr, Str, Facades\Event
};
use Laramore\Elements\TypeElement;
use Laramore\Facades\{
    Option, Type
};
use Laramore\Contracts\{
    Eloquent\LaramoreMeta, Field\Field, Configured
};
use Laramore\Contracts\Field\{
    RelationField, ExtraField
};
use Laramore\Traits\{
    IsOwned, IsLocked, HasProperties, HasOptions, HasLockedMacros
};
use Laramore\Fields\Constraint\FieldConstraintHandler;
use Laramore\Exceptions\ConfigException;

abstract class BaseField implements Field, Configured
{
    use IsOwned, IsLocked, HasLockedMacros, HasProperties, HasOptions {
        ownedBy as protected ownedByFromTrait;
        setOwner as protected setOwnerFromTrait;
        lock as protected lockFromTrait;
        setProperty as protected forceProperty;
        HasLockedMacros::__call as protected callMacro;
        HasProperties::__call as protected callProperty;
    }

    /**
     * Model that owns this field.
     *
     * @var \Laramore\Contracts\Eloquent\LaramoreModel
     */
    protected $model;

    /**
     * Default value of this field.
     *
     * @var mixed
     */
    protected $default;

    /**
     * Constraint handler.
     *
     * @var FieldConstraintHandler
     */
    protected $constraintHandler;

    /**
     * Create a new field with basic properties.
     * The constructor is protected so the field is created writing left to right.
     * ex: Char::field()->maxLength(255) insteadof (new Char)->maxLength(255).
     *
     * @param array $properties
     */
    protected function __construct(array $properties=[])
    {
        $this->addOptions($this->getType()->getDefaultOptions());

        $this->initProperties(\array_merge(
            $this->getConfig(),
            $properties
        ));

        $this->setConstraintHandler();
    }

    /**
     * Call the constructor and generate the field.
     *
     * @param  array $properties
     * @return self
     */
    public static function field(array $properties=[])
    {
        $creating = Event::until('fields.creating', static::class, \func_get_args());

        if ($creating === false) {
            return null;
        }

        $field = $creating ?: new static($properties);

        Event::dispatch('fields.created', $field);

        return $field;
    }

    /**
     * Return the configuration path for this field.
     *
     * @param string $path
     * @return mixed
     */
    public function getConfigPath(string $path=null)
    {
        return 'field.configurations.'.static::class.(\is_null($path) ? '' : '.'.$path);
    }

    /**
     * Return the configuration for this field.
     *
     * @param string $path
     * @param mixed  $default
     * @return mixed
     */
    public function getConfig(string $path=null, $default=null)
    {
        return config($this->getConfigPath($path), $default);
    }

    /**
     * Define all options for this field.
     *
     * @param array $options
     * @return self
     */
    public function options(array $options)
    {
        $this->needsToBeUnlocked();

        $this->options = [];
        $this->addOptions($options);

        return $this;
    }

    /**
     * Return the type derived of this field.
     *
     * @return TypeElement
     */
    protected function resolveType(): TypeElement
    {
        $type = $this->getConfig('type');

        if (\is_null($type)) {
            throw new ConfigException($this->getConfigPath('type'), \array_keys(Type::all()), null);
        }

        return Type::get($type);
    }

    /**
     * Return the type object of the field.
     *
     * @return TypeElement
     */
    public function getType(): TypeElement
    {
        return $this->resolveType();
    }

    /**
     * Return a property by its name.
     *
     * @param  string $key
     * @return mixed
     * @throws \ErrorException If no property exists with this name.
     */
    public function getProperty(string $key)
    {
        if ($key === 'type') {
            return \call_user_func([$this, 'getType']);
        } else if ($key === 'native' || $key === 'attname') {
            return \call_user_func([$this, 'getNative']);
        } else if ($key === 'reversed' && \method_exists($this, 'getReversed')) {
            return \call_user_func([$this, 'getReversed']);
        }

        if ($this->hasProperty($key)) {
            if (\method_exists($this, $method = 'get'.\ucfirst($key))) {
                return \call_user_func([$this, $method]);
            }

            return $this->$key;
        } else if (Option::has($snakeKey = Str::snake($key))) {
            return $this->hasOption($snakeKey);
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

        if (Option::has($snakeKey = Str::snake($key))) {
            if ($value === false) {
                return $this->removeOption($snakeKey);
            }

            return $this->addOption($snakeKey);
        }

        return $this->forceProperty($key, $value);
    }

    /**
     * Parse the name value.
     *
     * @param  string $name
     * @return string
     */
    public static function parseName(string $name): string
    {
        return Str::replaceInTemplate(config('field.templates.name'), compact('name'));
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
     * Return the native value of this field.
     * Commonly, its name.
     *
     * @return string
     */
    public function getNative(): string
    {
        return $this->name;
    }

    /**
     * Return the fully qualified name.
     *
     * @return string
     */
    public function getQualifiedName(): string
    {
        $this->needsToBeOwned();

        return $this->getMeta()->getTableName().'.'.$this->getNative();
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

        $this->removeOption(Option::required());

        if (\is_null($value)) {
            $this->addOption(Option::nullable());
        }

        $this->defineProperty('default', $this->cast($value));

        return $this;
    }

    /**
     * Get a default value for this field.
     *
     * @return mixed
     */
    public function getDefault()
    {
        $value = $this->default;

        if (\is_object($value)) {
            return clone $value;
        }

        if (\is_callable($value)) {
            return $value($this);
        }

        return $value;
    }

    /**
     * Create a Constraint handler for this meta.
     *
     * @return void
     */
    protected function setConstraintHandler()
    {
        $this->constraintHandler = new FieldConstraintHandler($this);
    }

    /**
     * Return the relation handler for this meta.
     *
     * @return FieldConstraintHandler
     */
    public function getConstraintHandler()
    {
        if ($this->isOwned()) {
            return $this->getMeta()->getConstraintHandler()->getFieldHandler($this->getName());
        }

        return $this->constraintHandler;
    }

    /**
     * Set the owner.
     *
     * @param mixed $owner
     * @return void
     */
    protected function setOwner($owner)
    {
        $this->setOwnerFromTrait($owner);

        if (!$this->hasProperty('model')) {
            while (!($owner instanceof LaramoreMeta)) {
                $owner = $owner->getOwner();
            }

            $this->setMeta($owner);
        }

        // Only define the owner if it is different from the meta.
        if ($this->owner === $this->getMeta()) {
            $this->owner = $this->model;
        }
    }

    /**
     * Assign a unique owner to this instance.
     *
     * @param  mixed  $owner
     * @param  string $name
     * @return self
     */
    public function ownedBy($owner, string $name)
    {
        $this->needsToBeUnlocked();

        $name = static::parseName($name);

        $owning = Event::until('fields.owning', $this, $owner, $name);

        if ($owning === false) {
            return $this;
        }

        $this->ownedByFromTrait(($owning[0] ?? $owner), ($owning[1] ?? $name));

        Event::dispatch('fields.owned', $this);

        return $this;
    }

    /**
     * Return the owner of this instance.
     *
     * @return mixed
     */
    public function getOwner()
    {
        $owner = $this->owner;

        if (\is_string($owner) && $owner === $this->model) {
            return $owner::getMeta();
        }

        return $owner;
    }

    /**
     * Callaback when the instance is owned.
     *
     * @return void
     */
    protected function owned()
    {
        $this->getMeta()->getConstraintHandler()->addFieldHandler($this->constraintHandler);
        unset($this->constraintHandler);

        $owner = $this->getOwner();

        if (!($owner instanceof LaramoreMeta) && !($owner instanceof BaseComposed)) {
            throw new \LogicException('A field should be owned by a LaramoreMeta or a BaseComposed');
        }
    }

    /**
     * Disallow any modifications after locking the instance.
     *
     * @return self
     */
    public function lock()
    {
        $this->needsToBeOwned();

        $locking = Event::until('fields.locking', $this);

        if ($locking === false) {
            return $this;
        }

        $this->lockFromTrait();

        Event::dispatch('fields.locked', $this);

        return $this;
    }

    /**
     * Each class locks in a specific way.
     *
     * @return void
     */
    protected function locking()
    {
        $this->checkOptions();
        $this->setProxies();
    }

    /**
     * Check all properties and options before locking the field.
     *
     * @return void
     */
    protected function checkOptions()
    {
        $name = $this->getQualifiedName();

        if ($this->hasProperty('default')) {
            if (\is_null($this->getDefault())) {
                if ($this->hasOption(Option::notNullable())) {
                    throw new \LogicException("The field `$name` cannot be null and defined as null by default");
                } else if (!$this->hasOption(Option::nullable()) && !$this->hasOption(Option::required())) {
                    throw new \LogicException("The field `$name` cannot be null, defined as null by default and not required");
                }
            } else if ($this->hasOption(Option::required())) {
                throw new \LogicException("The field `$name` cannot have a default value and be required");
            }
        }

        if (!$this->hasOption(Option::fillable()) && $this->hasOption(Option::required())) {
            throw new \LogicException("The field `$name` must be fillable if it is required");
        }

        if ($this->hasOption(Option::notNullable()) && $this->hasOption(Option::nullable())) {
            throw new \LogicException("The field `$name` cannot be nullable and not nullable on the same time");
        }

        if ($this->hasOption(Option::append()) && !($this instanceof ExtraField)) {
            throw new \LogicException("The field `$name` cannot be appended if it is not an extra field");
        }

        if (($this->hasOption(Option::with()) || $this->hasOption(Option::withCount())) && !($this instanceof RelationField)) {
            throw new \LogicException("The field `$name` cannot be autoloaded if it is not a relation field");
        }
    }

    /**
     * Define all proxies for this field.
     *
     * @return void
     */
    protected function setProxies()
    {
        $proxyHandler = $this->getMeta()->getProxyHandler();

        $class = $this->getConfig('proxy.class') ?: config('field.proxy.class');
        $proxies = \array_merge(config('field.proxy.configurations'), $this->getConfig('proxy.configurations', []));

        foreach ($proxies as $methodName => $data) {
            if (\is_null($data)) {
                continue;
            }

            $templates = Arr::get($data, 'templates', []);

            $proxyHandler->add(new $class(
                $this, $methodName,
                Arr::get($data, 'static', false),
                Arr::get($data, 'allow_multi', true),
                Arr::get($data, 'needs_value', false),
                Arr::get($templates, 'name'), Arr::get($templates, 'multi_name')
            ));
        }
    }

    /**
     * Define the meta of this field.
     *
     * @param  LaramoreMeta $meta
     * @return self
     */
    public function setMeta(LaramoreMeta $meta)
    {
        $this->needsToBeUnlocked();

        if ($this->hasProperty('model')) {
            throw new \LogicException('The meta cannot be defined multiple times');
        }

        $this->defineProperty('model', $meta->getModelClass());

        return $this;
    }

    /**
     * Return the meta of this field.
     * The owner could be a composed field and so on but not the coresponded meta.
     *
     * @return LaramoreMeta
     */
    public function getMeta(): LaramoreMeta
    {
        return $this->model::getMeta();
    }

    /**
     * Return a property, or set one.
     *
     * @param  string $method
     * @param  array  $args
     * @return mixed
     */
    public function __call(string $method, array $args)
    {
        if (static::hasMacro($method)) {
            return $this->callMacro($method, $args);
        }

        return $this->callProperty($method, $args);
    }

    /**
     * Return the native value of this field.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getNative();
    }

    /**
     * Clone this field.
     *
     * @return void
     */
    public function __clone()
    {
        $this->owner = null;
        $this->model = null;
        $this->locked = false;

        $this->setConstraintHandler();
    }
}
