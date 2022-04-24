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
use Laramore\Contracts\Field\{
    ExtraField, IncrementField, NumericField, RelationField
};
use Laramore\Fields\{
    BaseField,
    DateTime, Constraint\ConstraintHandler, Constraint\Primary
};
use Laramore\Contracts\{
    Eloquent\LaramoreMeta, Field\Field
};
use Laramore\Facades\Meta as MetaManager;
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
    protected $modelGroup;
    protected $modelName;
    protected $modelConfig;

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

        $this->description = $this->description ?: $this->modelName;
        $this->tableName = $this->getDefaultTableName();

        $this->setConstraintHandler();

        Event::dispatch('meta.created', $this);
    }

    /**
     * Set model class and generate model group and name.
     *
     * @param string $modelClass
     * @return void
     */
    public function setModelClass(string $modelClass)
    {
        $this->modelClass = $modelClass;

        foreach (MetaManager::getFacadeRoot()::$modelsPaths as $path) {
            $namespace = \str_replace('/', '\\', Str::title($path)).'\\';

            if (Str::startsWith($this->modelClass, $namespace)) {
                $base = Str::replaceFirst($namespace, '', $this->modelClass);
                $elements = explode('\\', $base);

                $this->modelName = Str::snake(\array_pop($elements));
                $this->modelGroup = \count($elements) == 0 ? null : Str::snake(implode('_', $elements));

                return;
            }
        }

        $this->modelGroup = null;
        $this->modelName = Str::snake(\class_basename($this->modelClass));
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
     * Get the model group name.
     *
     * @return string|null
     */
    public function getModelGroup(): ?string
    {
        return $this->modelGroup;
    }

    /**
     * Get the model short name.
     *
     * @return string
     */
    public function getModelName(): string
    {
        return $this->modelName;
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
        }, \array_merge(
            \is_null($this->getModelGroup()) ? [] : \explode('_', $this->getModelGroup()),
            \explode('_', $this->modelName),
        )));
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
            if ($field->getName() != $name) {
                throw new \Exception("The field name must be the same as the given one, expecting `{$field->getName()}`, got `$name`.");
            }

            if ($field->getMeta() != $this) {
                throw new \LogicException("The field `$name` is already".'owned by another meta.');
            }
        } else {
            $field->ownedBy($this, $name);
        }

        $name = $field->getName();

        if ($this->hasField($name)) {
            throw new \LogicException("The field `$name` is already defined for model `{$this->modelClass}`.");
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
            if ($field->getNative() == $nativeName
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
     * @param  string $class The field must be an instance of the class.
     * @return array
     */
    public function getFieldsWithOption(string $option, string $class=null): array
    {
        $fields = [];

        foreach ($this->getFields($class) as $field) {
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
     * @param  string $class The field must be an instance of the class.
     * @return array
     */
    public function getFieldNamesWithOption(string $option, string $class=null): array
    {
        return \array_map(function ($field) {
            return $field->getName();
        }, $this->getFieldsWithOption($option, $class));
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
            call_user_func($this->afterPreparating, $this);
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
            if ($field->getOwner() == $this) {
                $field->lock();
            }
        }

        if (!$this->hasTimestamps()) {
            $this->hasTimestamps = $this->hasField($this->modelClass::CREATED_AT)
                && $this->hasField($this->modelClass::UPDATED_AT);
        }

        if (!$this->hasDeletedTimestamp()) {
            $this->hasDeletedTimestamp = $this->hasField($this->modelClass::DELETED_AT);
        }

        $this->generateModelConfig();
    }

    protected function generateModelConfig()
    {
        $this->modelConfig = [
            'fillable' => $this->getFieldNamesWithOption('fillable'),
            'visible' => $this->getFieldNamesWithOption('visible'),
            'required' => $this->getFieldNamesWithOption('required'),
            'with' => ($with = $this->getFieldNamesWithOption('with', RelationField::class)),
            'with_count' => $this->getFieldNamesWithOption('with_count'),
            'appends' => $this->getFieldNamesWithOption('append', ExtraField::class),
            'select' => $this->getFieldNamesWithOption('select'),

            'timestamps' => $this->hasTimestamps(),

            'table' => $this->getTableName(),
            'connection' => $this->getConnectionName(),

            'incrementing' => false,
            'key_type' => 'int',
        ];

        if ($primary = $this->getPrimary()) {
            $this->modelConfig['incrementing'] = !$primary->isComposed() && $primary->getAttribute() instanceof IncrementField;
            $this->modelConfig['key_type'] = $primary->getAttribute() && $primary->getAttribute() instanceof NumericField ? 'int' : 'string';
        }
    }

    public function getModelConfig()
    {
        return $this->modelConfig;
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
            DateTime::field()->options(['select', 'not_nullable', 'visible', 'use_current'])
        );

        $this->setField(
            $updatedField,
            $updatedField = DateTime::field()->options($autoUpdated ? ['select', 'not_nullable', 'visible'] : ['select', 'nullable', 'visible'])
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

        $deletedName = $this->modelClass::DELETED_AT;

        if ($this->hasField($deletedName)) {
            if (!$this->hasField($deletedName, DateTime::class)) {
                throw new MetaException($this, "The deleted field `$deletedName` already exists and can't be set as a timestamp.");
            }
        } else {
            $this->setField(
                $deletedName,
                DateTime::field()->options(['select', 'nullable', 'visible'])
            );
        }

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
        return $this->getField(BaseField::parseName($name));
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
