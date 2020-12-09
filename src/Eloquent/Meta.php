<?php
/**
 * Defines all meta data for a specific model.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Eloquent;

use Illuminate\Support\{
    Str, Facades\Event
};
use Laramore\Exceptions\MetaException;
use Laramore\Facades\FieldConstraint;
use Laramore\Fields\{
    DateTime, Constraint\ConstraintHandler, Constraint\Primary
};
use Laramore\Contracts\{
    Eloquent\LaramoreMeta, Field\Field
};
use Laramore\Traits\{
    IsPrepared, IsLocked, HasLockedMacros, Eloquent\HasFields, Eloquent\HasFieldsConstraints
};

class Meta implements LaramoreMeta
{
    use IsPrepared, IsLocked, HasLockedMacros, HasFields, HasFieldsConstraints {
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
    protected $description;
    protected $tableName;
    protected $connectionName;

    /**
     * All fields: attributes, composites and links.
     *
     * @var array
     */
    protected $fields = [];

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
     * Callback when the meta has been prepared.
     *
     * @var callback|\Closure
     */
    protected $afterPreparating;

    /**
     * Create a Meta for a specific model.
     *
     * @param string $modelClass
     */
    public function __construct(string $modelClass)
    {
        Event::dispatch('meta.creating', static::class, \func_get_args());

        $this->setModelClass($modelClass);
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
        $this->modelClassName = Str::snake(\class_basename($modelClass));
        $this->description = $this->description ?: $this->modelClassName;

        $this->tableName = $this->getDefaultTableName();
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
    public function getModelClassName(): string
    {
        return $this->modelClassName;
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
     * Return the primary constraint.
     *
     * @return Primary|null
     */
    public function getPrimary()
    {
        return $this->getConstraintHandler()->getPrimary();
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
     * Return the description.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Define the description.
     *
     * @param string $description
     * @return self
     */
    public function setDescription(string $description)
    {
        $this->needsToBeUnlocked();

        $this->description = $description;

        return $this;
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
     * Return the connection name.
     *
     * @return string|null
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * Define the connection name.
     *
     * @param string $connectionName
     * @return self
     */
    public function setConnectionName(string $connectionName=null)
    {
        $this->needsToBeUnlocked();

        $this->connectionName = $connectionName;

        return $this;
    }

    /**
     * Define a field with a given name.
     *
     * @param string $name
     * @param Field  $field
     * @return self
     */
    public function setField(string $name, Field $field)
    {
        $this->needsToBeUnlocked();

        if ($field->isOwned()) {
            if ($field->getName() !== $name) {
                throw new \Exception("The field name must be the same as the given one, expecting `{$field->getName()}`, got `$name`.");
            }

            if ($field->getMeta() !== $this) {
                throw new \LogicException("The field `$name` is already".'owned by another meta.');
            }
        } else {
            $field->ownedBy($this, $name);
        }

        $name = $field->getName();

        if ($this->hasField($name)) {
            throw new \LogicException("The field `$name` is already defined.");
        }

        $this->fields[$name] = $field;

        return $this;
    }

    /**
     * Indicate if this composed has a field or a link.
     *
     * @param  string $name
     * @param  string $class The field must be an instance of the class.
     * @return boolean
     */
    public function hasField(string $name, string $class=null): bool
    {
        return isset($this->getFields()[$name])
            && (\is_null($class) || ($this->getFields()[$name] instanceof $class));
    }

    /**
     * Return the field or link with the given name.
     *
     * @param  string $name
     * @param  string $class The field must be an instance of the class.
     * @return Field
     */
    public function getField(string $name, string $class=null): Field
    {
        if ($this->hasField($name, $class)) {
            return $this->getFields()[$name];
        } else {
            throw new \Exception("The field `$name` does not exist");
        }
    }

    /**
     * Return the field with its native name.
     *
     * @param  string $nativeName
     * @param  string $class      The field must be an instance of the class.
     * @return Field
     */
    public function findField(string $nativeName, string $class=null): Field
    {
        foreach ($this->getFields() as $field) {
            if ($field->getNative() === $nativeName
                && (\is_null($class) || ($field instanceof $class))) {
                return $field;
            }
        }

        throw new \Exception("The native field `$nativeName` does not exist");
    }

    /**
     * Return getFields sub attributes and links.
     *
     * @param  string $class The field must be an instance of the class.
     * @return array<Field>
     */
    public function getFields(string $class=null): array
    {
        if (!\is_null($class)) {
            return \array_filter($this->fields, function ($field) use ($class) {
                return $field instanceof $class;
            });
        }

        return $this->fields;
    }

    /**
     * Return all fields with a specific option.
     *
     * @param  string $option
     * @return array
     */
    public function getFieldsWithOption(string $option): array
    {
        $fields = [];

        foreach ($this->getFields() as $field) {
            if ($field->hasOption($option)) {
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
    public function getFieldNamesWithOption(string $option): array
    {
        return \array_map(function ($field) {
            return $field->getName();
        }, $this->getFieldsWithOption($option));
    }

    /**
     * Add after preparing callback.
     *
     * @param callback|\Closure $callback
     * @return self
     */
    public function after($callback)
    {
        $this->needsToBeUnprepared();

        $this->afterPreparating = $callback;

        return $this;
    }

    /**
     * Prepare all fields.
     *
     * @return void
     */
    protected function preparing()
    {
        $this->getModelClass()::meta($this);
    }

    /**
     * Execute callback.
     *
     * @return void
     */
    protected function prepared()
    {
        if (!\is_null($this->afterPreparating)) {
            $this->afterPreparating($this);
        }
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
        if (\is_null($this->getPrimary()) && !$this->isPivot()) {
            throw new MetaException($this, 'A meta needs a primary key or must be set as pivot.');
        }

        foreach ($this->getFields() as $field) {
            if ($field->getOwner() === $this) {
                $field->lock();
            }
        }

        if (!$this->hasTimestamps()) {
            $this->hasTimestamps = $this->hasField($this->modelClass::CREATED_AT)
                && $this->hasField($this->modelClass::UPDATED_AT);
        }

        if (!$this->hasDeletedTimestamp()) {
            $key = \defined("{$this->getModelClass()}::DELETED_AT") ? $this->modelClass::DELETED_AT : 'deleted_at';

            $this->hasDeletedTimestamp = $this->hasField($key);
        }
    }

    /**
     * Add default timestamp fields.
     *
     * @param boolean $autoUpdated
     * @return self
     */
    public function useTimestamps(bool $autoUpdated=false)
    {
        $createdName = $this->modelClass::CREATED_AT;
        $updatedField = $this->modelClass::UPDATED_AT;

        if ($this->hasField($createdName)) {
            throw new MetaException($this, "The created field `$createdName` already exists and can't be set as a timestamp.");
        }

        if ($this->hasField($updatedField)) {
            throw new MetaException($this, "The updated field `$updatedField` already exists and can't be set as a timestamp.");
        }

        $this->setField(
            $createdName,
            DateTime::field()->options(['not_nullable', 'visible', 'use_current'])
        );

        $this->setField(
            $updatedField,
            $updatedField = DateTime::field()->options($autoUpdated ? ['not_nullable', 'visible'] : ['nullable', 'visible'])
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
     * @param boolean $useTimestamps
     * @param boolean $autoUpdated
     * @return self
     */
    public function useDeleteTimestamp(bool $useTimestamps=false, bool $autoUpdated=false)
    {
        if ($useTimestamps) {
            $this->useTimestamps($autoUpdated);
        }

        $deletedName = \defined("{$this->getModelClass()}::DELETED_AT") ? $this->modelClass::DELETED_AT : 'deleted_at';

        if ($this->hasField($deletedName)) {
            throw new MetaException($this, "The deleted field `$deletedName` already exists and can't be set as a timestamp.");
        }

        $this->setField(
            $deletedName,
            DateTime::field()->options(['nullable', 'visible'])
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
     * Indicate the this meta is not a pivot one.
     *
     * @return boolean
     */
    public function isPivot(): bool
    {
        return false;
    }

    /**
     * Return the field with a given name.
     *
     * @param  string $name
     * @return Field
     */
    public function __get(string $name): Field
    {
        return $this->getField($name);
    }

    /**
     * Set a field with a given name.
     *
     * @param string $name
     * @param Field  $value
     * @return self
     */
    public function __set(string $name, Field $value=null)
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

        if (\preg_match('/^(.*)FieldValue$/', $method, $matches)) {
            return $this->callFieldValueMethod(\array_shift($args), $matches[1], $args);
        }

        throw new \BadMethodCallException("The method `$method` does not exist.");
    }
}
