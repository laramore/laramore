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
use Laramore\Fields\{
	BaseField, Field, CompositeField, LinkField, Timestamp
};
use Laramore\Interfaces\{
	IsAField, IsAPrimaryField, IsAFieldOwner, IsProxied, IsALaramoreModel
};
use Laramore\Traits\IsLocked;
use Laramore\Traits\Meta\HasFields;
use Laramore\Traits\Model\HasLaramore;
use Laramore\Eloquent\{
	ModelEvent, ModelEventHandler
};
use Laramore\Validations\ValidationHandler;
use Laramore\Proxies\{
	BaseProxy, MetaProxy, MultiProxy, ProxyHandler
};
use Laramore\Template;
use ModelEvents, Validations, Proxies;

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
     * Indicate if this meta is a pivot one.
     *
     * @var bool
     */
    protected $pivot = false;

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
        Proxies::createHandler($this->modelClass);
    }

    /**
     * Create a Validation handler for this meta.
     *
     * @return void
     */
    protected function setValidationHandler()
    {
        Validations::createHandler($this->modelClass);
    }

    /**
     * Define all default observers:
     * - Auto fill all fields with their default value.
     *
     * @return void
     */
    protected function setDefaultObservers()
    {
        ModelEvents::createHandler($this->modelClass);

        $this->getModelEventHandler()->add(new ModelEvent('autofill_default', function (IsALaramoreModel $model) {
            $attributes = $model->getAttributes();

            foreach ($this->getFields() as $field) {
                if (!isset($attributes[$attname = $field->attname])) {
                    if ($field->hasProperty('default')) {
                        $model->setAttribute($attname, $field->default);
                    }
                }
            }
        }, ModelEvent::HIGH_PRIORITY, 'saving'));
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
     * Return the model observable handler for this meta.
     *
     * @return ModelEventHandler
     */
    public function getModelEventHandler(): ModelEventHandler
    {
        return ModelEvents::getHandler($this->getModelClass());
    }

    /**
     * Return the validation handler for this meta.
     *
     * @return ValidationHandler
     */
    public function getValidationHandler(): ValidationHandler
    {
        return Validations::getHandler($this->getModelClass());
    }

    /**
     * Return the validation handler for this meta.
     *
     * @return ProxyHandler
     */
    public function getProxyHandler(): ProxyHandler
    {
        return Proxies::getHandler($this->getModelClass());
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
        return isset($this->all()[$name]);
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
            return $this->all()[$name];
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
    public function all(): array
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

        foreach ($this->all() as $field) {
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
        return \array_map(function ($field) {
            return $field->name;
        }, $this->getFieldsWithOption
        ($option));
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
     * @return Field|null
     */
    public function getPrimary(): ?IsAPrimaryField
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
        if (\is_null($this->primary) && !$this->pivot) {
            throw new MetaException($this, 'A meta needs a primary key or must be set as pivot.');
        }

        foreach ($this->all() as $field) {
            if ($field->getOwner() === $this) {
                $field->lock();
            }

            $this->setFieldAttributeProxy('get', $field, ['instance'], ['model'], 'attribute');
            $this->setFieldAttributeProxy('set', $field, ['instance'], ['model'], 'attribute');
            $this->setFieldAttributeProxy('reset', $field, ['instance'], ['model'], 'attribute');
            $this->setFieldAttributeProxy('transform', $field);
            $this->setFieldAttributeProxy('serialize', $field);
            $this->setFieldAttributeProxy('check', $field);
            $this->setFieldAttributeProxy('dry', $field);
            $this->setFieldAttributeProxy('cast', $field);
            $this->setFieldAttributeProxy('default', $field);
        }
    }

    protected function setFieldAttributeProxy(string $methodName, BaseField $field, array $injections=[], array $on=['model'], string $secondPart='')
    {
        return $this->setProxy("${methodName}FieldAttribute", $this->generateProxyMethodName($field, $methodName, $secondPart), $field, array_merge(['field'], $injections), $on);
    }

    protected function setProxy(string $methodName, string $proxyName, BaseField $field, array $injections=[], array $on=['model'])
    {
        $proxy = new MetaProxy($proxyName, $this, $field, $methodName, $injections, $on);

        $this->getProxyHandler()->add($proxy);

        return $proxy;
    }

    protected function generateProxyMethodName(BaseField $field, string $firstPart, string $secondPart='')
    {
        return $firstPart.\ucfirst(Str::camel($field->name)).\ucfirst($secondPart);
    }

    /**
     * Define the primary field.
     *
     * @param  Field $field
     * @return self
     */
    public function primary(IsAPrimaryField $field)
    {
        if ($this->primary) {
            throw new LaramoreException('A primary field is already set');
        }

        $this->primary = $field;

        return $this;
    }

    /**
     * Define a unique relation between multiple fields.
     *
     * @param  array fields
     * @return self
     */
    public function unique(array $fields)
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
                        return $this->unique($field->getFields());
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
    public function useTimestamps($autoUpdated=false)
    {
        $createdName = ($this->modelClass::CREATED_AT ?? 'created_at');
        $updatedField = ($this->modelClass::UPDATED_AT ?? 'updated_at');

        if ($this->has($createdName)) {
            throw new MetaException($this, "The field [$createdName] already exists and can't be set as a timestamp.");
        }

        if ($this->has($updatedField)) {
            throw new MetaException($this, "The field [$updatedField] already exists and can't be set as a timestamp.");
        }

        $this->set(
            $createdName,
            Timestamp::field(['not_nullable', 'visible', 'use_current'])
        );

        $this->set(
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
    public function __set(string $name, BaseField $value=null)
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

    protected function getProxyInjection(BaseProxy $proxy, string $argName, ?IsProxied $proxiedInstance=null)
    {
        switch ($argName) {
            case 'instance':
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
            $proxy = $proxy->get(\array_shift($args));
        }

        $field = $proxy->getField();
        $methodName = $proxy->getMethodName();

        foreach (\array_reverse($proxy->getInjections()) as $name) {
            \array_unshift($args, $this->getProxyInjection($proxy, $name, $proxiedInstance));
        }

        return $proxy(...$args);
    }
}
