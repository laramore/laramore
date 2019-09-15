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
use Illuminate\Database\Eloquent\Model;
use Laramore\Exceptions\{
	MultipleExceptionsException, RequiredFieldException
};
use Laramore\Facades\{
	ModelEventManager, ValidationManager, ProxyManager
};
use Laramore\Fields\{
	BaseField, Field, CompositeField, Link\LinkField, Timestamp
};
use Laramore\Interfaces\{
	IsAField, IsAPrimaryField, IsAFieldOwner, IsProxied
};
use Laramore\Traits\IsLocked;
use Laramore\Traits\Meta\HasFields;
use Laramore\Traits\Model\HasLaramore;
use Laramore\Models\{
	ModelEvent, ModelEventHandler
};
use Laramore\Validations\ValidationHandler;
use Laramore\Proxies\{
	BaseProxy, MultiProxy, ProxyHandler
};
use Laramore\Template;

class Meta implements IsAFieldOwner
{
    use IsLocked, HasFields;

    /**
     * All data relative to the model and the table.
     *
     * @var string
     */
    protected $modelClass;
    protected $modelClassName;
    protected $tableName;

    /**
     * All fields: classics, composites and links.
     *
     * @var array
     */
    protected $fields = [];
    protected $composites = [];
    protected $links = [];

    /**
     * Indicate if we use default timestamps.
     *
     * @var bool
     */
    protected $hasTimestamps = false;

    /**
     * All indexes.
     *
     * @var array
     */
    protected $primary;
    protected $indexes = [];
    protected $uniques = [];

    /**
     * Create a Meta for a specific model.
     *
     * @param string $modelClass
     */
    public function __construct(string $modelClass)
    {
        $this->setModelClass($modelClass);
        $this->setDefaultObservers();
        $this->setValidationHandler();
        $this->setProxyHandlers();

        if (config('database.table.timestamps', false)) {
            $this->useTimestamps();
        }
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

        $parts = \explode('\\', $modelClass);
        $this->modelClassName = \end($parts);

        $this->tableName = $this->getDefaultTableName();
    }

    /**
     * Create a Validation handler for this meta.
     *
     * @return void
     */
    protected function setProxyHandlers()
    {
        ProxyManager::createHandler($this->modelClass);
    }

    /**
     * Create a Validation handler for this meta.
     *
     * @return void
     */
    protected function setValidationHandler()
    {
        ValidationManager::createHandler($this->modelClass);
    }

    /**
     * Define all default observers:
     * - Auto fill all fields with their default value.
     * - Check that all required fields have a value.
     *
     * @return void
     */
    protected function setDefaultObservers()
    {
        ModelEventManager::createHandler($this->modelClass);

        $this->getModelEventHandler()->add(new ModelEvent('autofill_default', function (Model $model) {
            $attributes = $model->getAttributes();

            foreach ($this->getFields() as $field) {
                if (!isset($attributes[$attname = $field->attname])) {
                    if ($field->hasProperty('default')) {
                        $model->setAttribute($attname, $field->default);
                    }
                }
            }
        }, ModelEvent::HIGH_PRIORITY, 'saving'));

        $this->getModelEventHandler()->add(new ModelEvent('check_required_fields', function (Model $model) {
            $missingFields = \array_diff($this->getRequiredFieldNames(), \array_keys($model->getAttributes()));

            foreach ($missingFields as $key => $field) {
                if ($this->getField($field)->nullable) {
                     unset($missingFields[$key]);
                }
            }

            if (\count($missingFields)) {
                throw new MultipleExceptionsException($this, array_map(function ($name) {
                    return new RequiredFieldException($this->get($name), "The field $name is required");
                }, array_values($missingFields)));
            }
        }, ModelEvent::LOW_PRIORITY, 'saving'));
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
     * Return the field manager for this meta.
     *
     * @return FieldManager
     */
    public function getFieldManager(): FieldManager
    {
        return $this->fieldManager;
    }

    /**
     * Return the model observable handler for this meta.
     *
     * @return ModelEventHandler
     */
    public function getModelEventHandler(): ModelEventHandler
    {
        return ModelEventManager::getHandler($this->getModelClass());
    }

    /**
     * Return the validation handler for this meta.
     *
     * @return ValidationHandler
     */
    public function getValidationHandler(): ValidationHandler
    {
        return ValidationManager::getHandler($this->getModelClass());
    }

    /**
     * Return the validation handler for this meta.
     *
     * @return ProxyHandler
     */
    public function getProxyHandler(): ProxyHandler
    {
        return ProxyManager::getHandler($this->getModelClass());
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
     * Parse the attribute name.
     *
     * @param  string $name
     * @return string
     */
    public function parseAttname(string $name): string
    {
        return Str::snake($name);
    }

    /**
     * Indicate if the meta as a field with a given name.
     *
     * @param  string $name
     * @return boolean
     */
    public function hasField(string $name): bool
    {
        return isset($this->getFields()[$name]);
    }

    /**
     * Return the field with a given name.
     *
     * @param  string $name
     * @return Field
     */
    public function getField(string $name): Field
    {
        if ($this->hasField($name)) {
            return $this->getFields()[$name];
        } else {
            throw new \ErrorException("The field $name does not exist");
        }
    }

    /**
     * Define a specific field with a given name.
     *
     * @param string $name
     * @param Field  $field
     * @return self
     */
    public function setField(string $name, Field $field)
    {
        $this->needsToBeUnlocked();

        if ($this->has($name)) {
            throw new \LogicException("The field $name is already defined");
        }

        $field = $this->manipulateField($field)->own($this, $this->parseAttname($name));
        $this->fields[$field->name] = $field;

        return $this;
    }

    /**
     * Return all fields.
     *
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
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

        if ($this->has($name)) {
            throw new \Exception('It is not allowed to reset the field '.$name);
        }

        if ($link->isOwned()) {
            if ($link->name !== $name) {
                throw new \Exception('The link field name must be the same than the given one.');
            }
        } else {
            throw new \Exception('The link field must be owned by a child of the oposite meta.');
        }

        $link = $this->manipulateField($link);
        $this->links[$link->name] = $link;

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
        } else {
            throw new \Exception($name.' link field does not exist');
        }
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

        if ($this->has($name)) {
            throw new \Exception('It is not allowed to reset the field '.$name);
        }

        $composite = $this->manipulateField($composite)->own($this, $this->parseAttname($name));
        $this->composites[$composite->name] = $composite;

        foreach ($composite->getFields() as $field) {
            if (!$field->isOwned() || $field->getOwner() !== $composite) {
                throw new \Exception("The field $name must be owned by the composed field ".$value->name);
            }

            $this->fields[$field->name] = $this->manipulateField($field);
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
    public function has(string $name): bool
    {
        return isset($this->allFields()[$name]);
    }

    /**
     * Indicate if this meta has a classic, link or composite field with a given name.
     *
     * @param  string $name
     * @return BaseField
     */
    public function get(string $name): BaseField
    {
        if ($this->has($name)) {
            return $this->allFields()[$name];
        } else {
            throw new \Exception($name.' field does not exist');
        }
    }

    /**
     * Define a classic, link or composite field with a given name.
     *
     * @param string    $name
     * @param BaseField $field
     * @return self
     */
    public function set(string $name, BaseField $field)
    {
        if ($field instanceof CompositeField) {
            return $this->setComposite($name, $field);
        } else if ($field instanceof LinkField) {
            return $this->setLink($name, $field);
        } else if ($field instanceof Field) {
            return $this->setField($name, $field);
        }

        throw new \Exception('To set a specific field, you have to give a Field, LinkField or CompositeField');
    }

    /**
     * Return all classic, link or composite fields.
     *
     * @return array
     */
    public function allFields(): array
    {
        return array_merge(
	        $this->fields,
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
    public function getFieldsWithOption(string $option): array
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
    public function getFieldNamesWithOption(string $option): array
    {
        $fields = [];

        foreach ($this->getFields() as $field) {
            if ($field->$option) {
                $fields[] = $field->name;
            }
        }

        return $fields;
    }

    /**
     * Return all fillable fields.
     *
     * @return array
     */
    public function getFillableFields(): array
    {
        return $this->getFieldsWithOption('fillable');
    }

    /**
     * Return all visibile fields.
     *
     * @return array
     */
    public function getVisibleFields(): array
    {
        return $this->getFieldsWithOption('visible');
    }

    /**
     * Return all required fields.
     *
     * @return array
     */
    public function getRequiredFields(): array
    {
        return $this->getFieldsWithOption('required');
    }

    /**
     * Return all fillable fieldNames.
     *
     * @return array
     */
    public function getFillableFieldNames(): array
    {
        return $this->getFieldNamesWithOption('fillable');
    }

    /**
     * Return all visibile fieldNames.
     *
     * @return array
     */
    public function getVisibleFieldNames(): array
    {
        return $this->getFieldNamesWithOption('visible');
    }

    /**
     * Return all required fieldNames.
     *
     * @return array
     */
    public function getRequiredFieldNames(): array
    {
        return $this->getFieldNamesWithOption('required');
    }

    /**
     * Return all unique constraints.
     *
     * @return array
     */
    public function getUniques(): array
    {
        return $this->uniques;
    }

    /**
     * Return all indexes.
     *
     * @return array
     */
    public function getIndexes(): array
    {
        return $this->indexes;
    }

    /**
     * Return the primary field.
     *
     * @return Field
     */
    public function getPrimary(): ?Field
    {
        return $this->primary;
    }

    /**
     * Lock all owned fields.
     *
     * @return void
     */
    protected function locking()
    {
        foreach ($this->allFields() as $field) {
            if ($field->getOwner() === $this) {
                $field->lock();
            }

            // $this->add();
        }
    }

    /**
     * Define the primary field.
     *
     * @param  Field $field
     * @return self
     */
    public function primary(Field $field)
    {
        if ($this->primary) {
            throw new \Exception('A primary field is already set');
        }

        $this->primary = $field;

        return $this;
    }

    /**
     * Define a unique relation between multiple fields.
     *
     * @param  mixed ...$fields
     * @return self
     */
    public function unique(...$fields)
    {
        $unique = [];

        if (count($fields) > 0) {
            if (count($fields) > 1) {
                foreach ($fields as $field) {
                    if (is_string($field)) {
                        $unique[] = $this->getField($field);
                    } else if ($field instanceof CompositeField) {
                        if ($this->get($field->name) !== $field) {
                               throw new \Exception('It is not allowed to use external composite fields');
                        } else {
                            foreach ($field->getFields() as $compositeField) {
                                $unique[] = $compositeField;
                            }
                        }
                    } else if ($field instanceof IsAField) {
                        if ($this->get($field->name) !== $field) {
                            throw new \Exception('It is not allowed to use external field');
                        } else {
                            $unique[] = $field;
                        }
                    } else {
                        throw new \Exception('The field '.((string) $field).' was not recognized');
                    }
                }

                $this->uniques[] = $unique;
            } else {
                $field = $fields[0];

                if (is_string($field)) {
                    $this->getField($field)->unique();
                } else if ($field instanceof CompositeField) {
                    if ($this->get($field->name) !== $field) {
                        throw new \Exception('It is not allowed to use external composite fields');
                    } else {
                        return $this->unique(...$field->getFields());
                    }
                } else if ($field instanceof IsAField) {
                    if ($this->get($field->name) !== $field) {
                        throw new \Exception('It is not allowed to use external field');
                    } else {
                        $field->unique();
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Add default timestamp fields.
     *
     * @return self
     */
    public function useTimestamps()
    {
        try {
            $this->set(
	            ($this->modelClass::CREATED_AT ?? 'created_at'),
	            Timestamp::field(Field::NOT_NULLABLE | Field::VISIBLE)->useCurrent()
            );

            $this->set(
	            ($this->modelClass::UPDATED_AT ?? 'updated_at'),
            	Timestamp::field(Field::NULLABLE | Field::VISIBLE)
            );
        } catch (\Exception $e) {
            throw new \Exception('Can not set timestamps. Maybe already set ?');
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
     * Return the field with a given name.
     *
     * @param  string $name
     * @return BaseField
     */
    public function __get(string $name): BaseField
    {
        return $this->get($name);
    }

    /**
     * Set a field with a given name.
     *
     * @param string    $name
     * @param BaseField $value
     * @return self
     */
    public function __set(string $name, BaseField $value)
    {
        return $this->set($name, $value);
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
        if (\preg_match('/^(.*)FieldAttribute$/', $method, $matches)) {
            return $this->callFieldAttributeMethod(\array_shift($args), $matches[1], $args);
        }

        throw new \Exception("The method [$method] does not exist.");
    }

    protected function getProxyInjection(string $argName, ?IsProxied $proxiedInstance=null)
    {
        switch ($argName) {
            case 'instance':
                return $proxiedInstance;
            break;

            case 'value':
                return $proxiedInstance->getAttribute($field->attname);
            break;

            default:
                throw new \Exception("The proxy arg [$argName] does not exist.");
        }
    }

    public function proxyCall(BaseProxy $proxy, ?IsProxied $proxiedInstance=null, array $args=[])
    {
        if ($proxy instanceof MultiProxy) {
            $proxy = $proxy->get(\array_shift($args));
        }

        $field = $proxy->getField();
        $methodName = $proxy->getMethodName();

        foreach ($proxy->getInjections() as $name) {
            \array_unshift($args, $this->getProxyInjection($name, $proxiedInstance));
        }

        return $this->callFieldAttributeMethod($field, $methodName, $args);
    }
}
