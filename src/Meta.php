<?php
/**
 * Defines all meta data for a specific model.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore;

use Illuminate\Support\Str;
use Laramore\Exceptions\MetaException;
use Laramore\Facades\{
    FieldProxy, FieldConstraint
};
use Laramore\Fields\{
    BaseField, AttributeField, CompositeField, LinkField, Timestamp, Constraint\ConstraintHandler
};
use Laramore\Interfaces\{
    IsAPrimaryField, IsAFieldOwner, IsProxied
};
use Laramore\Traits\{
    IsLocked, HasLockedMacros
};
use Laramore\Traits\Meta\{
    HasFields, HandlesFieldConstraints
};
use Laramore\Fields\Proxy\{
    BaseProxy, MultiProxy, ProxyHandler
};
use Event;

class Meta implements IsAFieldOwner
{
    use IsLocked, HasLockedMacros, HasFields, HandlesFieldConstraints {
        IsLocked::lock as protected lockFromTrait;
        HasLockedMacros::__call as protected callMacro;
    }

    /**
     * All data relative to the model and the table.
     *
     * @var string
     */
    protected $modelClass;
    protected $modelClassName;
    protected $tableName;

    /**
     * All fields: attributes, composites and links.
     *
     * @var array
     */
    protected $attributes = [];
    protected $composites = [];
    protected $links = [];

    /**
     * Indicate if we use create/update timestamps.
     *
     * @var bool
     */
    protected $hasTimestamps = false;

    /**
     * Indicate if we use soft deletes.
     *
     * @var bool
     */
    protected $hasDeletedTimestamp = false;

    /**
     * Indicate if this meta is a pivot one.
     *
     * @var bool
     */
    protected $pivot = false;

    /**
     * Create a Meta for a specific model.
     *
     * @param string $modelClass
     */
    public function __construct(string $modelClass)
    {
        Event::dispatch('meta.creating', static::class, \func_get_args());

        $this->setModelClass($modelClass);
        $this->setProxyHandler();
        $this->setConstraintHandler();

        Event::dispatch('meta.created', $this);
    }

    /**
     * Define the model class name for this meta.
     *
     * @param string $modelClass
     * @return void
     */
    protected function setModelClass(string $modelClass)
    {
        $this->modelClass = $modelClass;
        $this->modelClassName = class_basename($modelClass);

        $this->tableName = $this->getDefaultTableName();
    }

    /**
     * Create a Validation handler for this meta.
     *
     * @return void
     */
    protected function setProxyHandler()
    {
        FieldProxy::createHandler($this->getModelClass());
    }

    /**
     * Create a Constraint handler for this meta.
     *
     * @return void
     */
    protected function setConstraintHandler()
    {
        FieldConstraint::createHandler($this->getModelClass());
    }

    /**
     * Return the model class.
     *
     * @return string
     */
    public function getModelClass(): string
    {
        return $this->modelClass;
    }

    /**
     * Get the model short name.
     *
     * @return string|null
     */
    public function getModelClassName(): ?string
    {
        return $this->modelClassName;
    }

    /**
     * Return the validation handler for this meta.
     *
     * @return ProxyHandler
     */
    public function getProxyHandler(): ProxyHandler
    {
        return FieldProxy::getHandler($this->getModelClass());
    }

    /**
     * Return the relation handler for this meta.
     *
     * @return ConstraintHandler
     */
    public function getConstraintHandler(): ConstraintHandler
    {
        return FieldConstraint::getHandler($this->getModelClass());
    }

    /**
     * Return the default table name for this meta.
     *
     * @return string
     */
    public function getDefaultTableName(): string
    {
        return \implode('_', \array_map(function ($element) {
            return Str::plural($element);
        }, \explode(' ', Str::snake($this->modelClassName, ' '))));
    }

    /**
     * Return the table name.
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Define the table name.
     *
     * @param string $tableName
     * @return self
     */
    public function setTableName(string $tableName)
    {
        $this->needsToBeUnlocked();

        $this->tableName = $tableName;

        return $this;
    }

    /**
     * Manipulate a field as primary ones.
     *
     * @param  BaseField $field
     * @return BaseField
     */
    protected function manipulateField(BaseField $field): BaseField
    {
        if ($field instanceof IsAPrimaryField) {
            $this->primary($field);
        }

        return $field;
    }

    /**
     * Indicate if the meta as an attribute field with a given name.
     *
     * @param  string $name
     * @return boolean
     */
    public function hasAttribute(string $name): bool
    {
        return isset($this->getAttributes()[$name]);
    }

    /**
     * Return the attribute field with a given name.
     *
     * @param  string $name
     * @return AttributeField
     */
    public function getAttribute(string $name): AttributeField
    {
        if ($this->hasAttribute($name)) {
            return $this->getAttributes()[$name];
        } else {
            throw new \ErrorException("The attribute field `$name` does not exist");
        }
    }

    /**
     * Define a specific attribute field with a given name.
     *
     * @param string         $name
     * @param AttributeField $field
     * @return self
     */
    public function setAttribute(string $name, AttributeField $field)
    {
        $this->needsToBeUnlocked();

        $field = $this->manipulateField($field)->own($this, $name);
        $name = $field->getName();

        if ($this->hasField($name)) {
            throw new \LogicException("The field $name is already defined");
        }

        $this->attributes[$name] = $field;

        return $this;
    }

    /**
     * Return all fields.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Indicate if the meta has a link field with a given name.
     *
     * @param  string $name
     * @return boolean
     */
    public function hasLink(string $name): bool
    {
        return isset($this->getLinks()[$name]);
    }

    /**
     * Return the link field with a given name.
     *
     * @param  string $name
     * @return LinkField
     */
    public function getLink(string $name): LinkField
    {
        if ($this->hasLink($name)) {
            return $this->getLinks()[$name];
        } else {
            throw new \Exception("The link field $name does not exist");
        }
    }

    /**
     * Define a link field with a given name.
     *
     * @param  string    $name
     * @param  LinkField $link
     * @return self
     */
    public function setLink(string $name, LinkField $link)
    {
        $this->needsToBeUnlocked();

        $link = $this->manipulateField($link);

        if ($link->isOwned()) {
            if ($link->getName() !== $name) {
                throw new \Exception('The link field name must be the same than the given one.');
            }
        } else {
            $link->own($this, $name);
            $name = $link->getName();
        }

        if ($this->hasField($name)) {
            throw new \Exception('It is not allowed to reset the field '.$name);
        }

        $this->links[$name] = $link;

        return $this;
    }

    /**
     * Return all link fields.
     *
     * @return array
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * Indicate if this meta has a composite field with a given name.
     *
     * @param  string $name
     * @return boolean
     */
    public function hasComposite(string $name)
    {
        return isset($this->getComposites()[$name]);
    }

    /**
     * Return a composite field with a given name.
     *
     * @param  string $name
     * @return CompositeField
     */
    public function getComposite(string $name): CompositeField
    {
        if ($this->hasComposite($name)) {
            return $this->getComposites()[$name];
        }

        throw new \Exception($name.' composite field does not exist');
    }

    /**
     * Define a composite field with a given name.
     *
     * @param string         $name
     * @param CompositeField $composite
     * @return self
     */
    public function setComposite(string $name, CompositeField $composite)
    {
        $this->needsToBeUnlocked();

        $composite = $this->manipulateField($composite)->own($this, $name);
        $name = $composite->getName();

        if ($this->hasField($name)) {
            throw new \Exception('It is not allowed to reset the field '.$name);
        }

        $this->composites[$name] = $composite;

        foreach ($composite->getAttributes() as $field) {
            if (!$field->isOwned() || $field->getOwner() !== $composite) {
                throw new \Exception("The field $name must be owned by the composed field ".$field->getName());
            }

            $this->attributes[$field->getName()] = $this->manipulateField($field);
        }

        return $this;
    }

    /**
     * Return all composite fields.
     *
     * @return array
     */
    public function getComposites(): array
    {
        return $this->composites;
    }

    /**
     * Indicate if this meta has a classic, link or composite field with a given name.
     *
     * @param  string $name
     * @return boolean
     */
    public function hasField(string $name): bool
    {
        return isset($this->getFields()[$name]);
    }

    /**
     * Indicate if this meta has a classic, link or composite field with a given name.
     *
     * @param  string $name
     * @return BaseField
     */
    public function getField(string $name): BaseField
    {
        if ($this->hasField($name)) {
            return $this->getFields()[$name];
        }

        throw new \Exception($name.' field does not exist');
    }

    /**
     * Define a classic, link or composite field with a given name.
     *
     * @param string    $name
     * @param BaseField $field
     * @return self
     */
    public function setField(string $name, BaseField $field)
    {
        if ($field instanceof CompositeField) {
            return $this->setComposite($name, $field);
        } else if ($field instanceof LinkField) {
            return $this->setLink($name, $field);
        } else if ($field instanceof AttributeField) {
            return $this->setAttribute($name, $field);
        }

        throw new \Exception('To set a specific field, you have to give a AttributeField, LinkField or CompositeField');
    }

    /**
     * Return all attribute, link and composite fields.
     *
     * @return array
     */
    public function getFields(): array
    {
        return array_merge(
            $this->attributes,
            $this->composites,
            $this->links
        );
    }

    /**
     * Return all fields with a specific option.
     *
     * @param  string $option
     * @return array
     */
    public function getFieldsWithRule(string $option): array
    {
        $fields = [];

        foreach ($this->getFields() as $field) {
            if ($field->$option) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * Return all field names with a specific option.
     *
     * @param  string $option
     * @return array
     */
    public function getFieldNamesWithRule(string $option): array
    {
        return \array_map(function ($field) {
            return $field->getNative();
        }, $this->getFieldsWithRule($option));
    }

    /**
     * Return all fillable fields.
     *
     * @return array
     */
    public function getFillableFields(): array
    {
        return $this->getFieldsWithRule('fillable');
    }

    /**
     * Return all visibile fields.
     *
     * @return array
     */
    public function getVisibleFields(): array
    {
        return $this->getFieldsWithRule('visible');
    }

    /**
     * Return all required fields.
     *
     * @return array
     */
    public function getRequiredFields(): array
    {
        return $this->getFieldsWithRule('required');
    }

    /**
     * Return all fillable fieldNames.
     *
     * @return array
     */
    public function getFillableFieldNames(): array
    {
        return $this->getFieldNamesWithRule('fillable');
    }

    /**
     * Return all visibile fieldNames.
     *
     * @return array
     */
    public function getVisibleFieldNames(): array
    {
        $names = $this->getFieldNamesWithRule('visible');

        if (!\in_array('pivot', $names)) {
            $names[] = 'pivot';
        }

        return $names;
    }

    /**
     * Return all required fieldNames.
     *
     * @return array
     */
    public function getRequiredFieldNames(): array
    {
        return $this->getFieldNamesWithRule('required');
    }

    /**
     * Disallow any modifications after locking the instance.
     *
     * @return self
     */
    public function lock()
    {
        $locking = Event::until('meta.locking', $this);

        if ($locking === false) {
            return $this;
        }

        $this->lockFromTrait();

        Event::dispatch('meta.locked', $this);

        return $this;
    }

    /**
     * Lock all owned fields.
     *
     * @return void
     */
    protected function locking()
    {
        if (\is_null($this->getPrimary()) && !$this->pivot) {
            throw new MetaException($this, 'A meta needs a primary key or must be set as pivot.');
        }

        foreach ($this->getFields() as $field) {
            if ($field->getOwner() === $this) {
                $field->lock();
            }
        }

        if (!$this->hasTimestamps()) {
            $this->hasTimestamps = $this->hasAttribute($this->modelClass::CREATED_AT) && $this->hasAttribute($this->modelClass::UPDATED_AT);
        }

        if (!$this->hasDeletedTimestamp()) {
            $this->hasDeletedTimestamp = $this->hasAttribute(\defined("{$this->getModelClass()}::DELETED_AT") ? $this->modelClass::DELETED_AT : 'deleted_at');
        }
    }

    /**
     * Add default timestamp fields.
     *
     * @return self
     */
    public function useTimestamps($autoUpdated=false)
    {
        $createdName = $this->modelClass::CREATED_AT;
        $updatedField = $this->modelClass::UPDATED_AT;

        if ($this->hasField($createdName)) {
            throw new MetaException($this, "The created field `$createdName` already exists and can't be set as a timestamp.");
        }

        if ($this->hasField($updatedField)) {
            throw new MetaException($this, "The updated field `$updatedField` already exists and can't be set as a timestamp.");
        }

        $this->setAttribute(
            $createdName,
            Timestamp::field(['not_nullable', 'visible', 'use_current'])
        );

        $this->setAttribute(
            $updatedField,
            $updatedField = Timestamp::field($autoUpdated ? ['not_nullable', 'visible'] : ['nullable', 'visible'])
        );

        if ($autoUpdated) {
            $updatedField->useCurrent();
        }

        $this->hasTimestamps = true;

        return $this;
    }

    /**
     * Indicate if the meta use default timestamps.
     *
     * @return boolean
     */
    public function hasTimestamps(): bool
    {
        return $this->hasTimestamps;
    }

    /**
     * Add default soft delete field.
     *
     * @return self
     */
    public function useDeleteTimestamp($useTimestamps=false, $autoUpdated=false)
    {
        if ($useTimestamps) {
            $this->useTimestamps($autoUpdated);
        }

        $deletedName = \defined("{$this->getModelClass()}::DELETED_AT") ? $this->modelClass::DELETED_AT : 'deleted_at';

        if ($this->hasField($deletedName)) {
            throw new MetaException($this, "The deleted field `$deletedName` already exists and can't be set as a timestamp.");
        }

        $this->setAttribute(
            $deletedName,
            Timestamp::field(['nullable', 'visible'])
        );

        $this->hasDeletedTimestamp = true;

        return $this;
    }

    /**
     * Indicate if the meta use soft deletes.
     *
     * @return boolean
     */
    public function hasDeletedTimestamp(): bool
    {
        return $this->hasDeletedTimestamp;
    }

    /**
     * Indicate if the meta use default timestamps.
     *
     * @return self
     */
    public function setPivot($pivot=true)
    {
        $this->pivot = $pivot;

        return $this;
    }

    /**
     * Indicate if the meta use default timestamps.
     *
     * @return boolean
     */
    public function isPivot(): bool
    {
        return $this->pivot;
    }

    protected function getProxyInjection(BaseProxy $proxy, string $argName, ?IsProxied $proxiedInstance=null)
    {
        switch ($argName) {
            case 'instance':
                if (\is_null($proxiedInstance)) {
                    throw new \Error("The proxy `{$proxy->getName()}` cannot be called statically");
                }
                return $proxiedInstance;
                break;

            case 'field':
                return $proxy->getField();
                break;

            case 'value':
                return $proxiedInstance->getAttribute($proxy->getField()->attname);
                break;

            default:
                throw new \Exception("The proxy arg [$argName] does not exist.");
        }
    }

    public function proxyCall(BaseProxy $proxy, ?IsProxied $proxiedInstance=null, array $args=[])
    {
        if ($proxy instanceof MultiProxy) {
            $proxy = $proxy->getProxy(\array_shift($args));
        }

        $injections = $proxy->getInjections();

        if (!\in_array('instance', $injections)) {
        }

        foreach (\array_reverse($injections) as $name) {
            \array_unshift($args, $this->getProxyInjection($proxy, $name, $proxiedInstance));
        }

        return $proxy(...$args);
    }

    /**
     * Return the field with a given name.
     *
     * @param  string $name
     * @return BaseField
     */
    public function __get(string $name): BaseField
    {
        return $this->getField($name);
    }

    /**
     * Set a field with a given name.
     *
     * @param string    $name
     * @param BaseField $value
     * @return self
     */
    public function __set(string $name, BaseField $value=null)
    {
        return $this->setField($name, $value);
    }

    /**
     * Set a field with a given name.
     *
     * @param string $method
     * @param array  $args
     * @return self
     */
    public function __call(string $method, array $args)
    {
        if (static::hasMacro($method)) {
            return $this->callMacro($method, $args);
        }

        if (\preg_match('/^(.*)FieldAttribute$/', $method, $matches)) {
            return $this->callFieldAttributeMethod(\array_shift($args), $matches[1], $args);
        }

        throw new \Exception("The method `$method` does not exist.");
    }
}
