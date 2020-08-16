<?php
/**
 * Define a composed field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

use Illuminate\Support\{
    Str, Arr, Facades\Event
};
use Laramore\Elements\OptionElement;
use Laramore\Exceptions\ConfigException;
use Laramore\Facades\Option;
use Laramore\Contracts\{
    Proxied, Eloquent\LaramoreModel
};
use Laramore\Contracts\Field\{
    Field, ComposedField, AttributeField, RelationField, ExtraField
};

abstract class BaseComposed extends BaseField implements ComposedField
{
    /**
     * AttributeField fields managed by this composed fields.
     *
     * @var array<AttributeField>
     */
    protected $fields = [];

    /**
     * Name of each field.
     *
     * @var array<string,string>
     */
    protected $templates;

    /**
     * Create a new field with basic options.
     * The constructor is protected so the field is created writing left to right.
     * ex: OneToMany::field()->on(User::class) insteadof (new OneToMany)->on(User::class).
     *
     * @param array $options
     * @param array $fields  Allow the user to define sub fields.
     */
    protected function __construct(array $options=null, array $fields=null)
    {
        parent::__construct($options);

        $fields = ($fields ?: $this->getConfig('fields'));

        if (\is_null($fields) || (\count($fields) && !Arr::isAssoc($fields))) {
            throw new ConfigException($this->getConfigPath('fields'), ['any associative array of fields'], $fields);
        }

        foreach ($fields as $name => $field) {
            if (!\is_string($name)) {
                throw new \Exception('Fields need names');
            }

            $this->createField($name, $field);
        }
    }

    /**
     * Call the constructor and generate the field.
     *
     * @param array $options
     * @param array $fields  Allow the user to define sub fields.
     * @return self
     */
    public static function field(array $options=null, array $fields=null)
    {
        $creating = Event::until('fields.creating', static::class, \func_get_args());

        if ($creating === false) {
            return null;
        }

        $field = $creating ?: new static($options, $fields);

        Event::dispatch('fields.created', $field);

        return $field;
    }

    /**
     * Create a field.
     *
     * @param  string             $name
     * @param  array|string|Field $fieldData
     * @return Field
     */
    protected function createField(string $name, $fieldData): Field
    {
        if (\is_array($fieldData)) {
            $field = $fieldData[0]::field($fieldData[1]);
        } else if (\is_string($fieldData)) {
            $field = $fieldData::field();
        }

        $this->setField($name, $field);

        return $field;
    }

    /**
     * Define a field with a given name.
     * Be carefull of how it is used.
     *
     * @param string $name
     * @param Field  $field
     * @return self
     */
    public function setField(string $name, Field $field)
    {
        $this->needsToBeUnlocked();

        $this->fields[$name] = $field;

        return $this;
    }

    /**
     * Indicate if this composed has a field.
     *
     * @param  string $name
     * @param  string $class The field must be an instance of the class.
     * @return boolean
     */
    public function hasField(string $name, string $class=null): bool
    {
        return isset($this->getFields()[$name]) && (
            \is_null($class) || ($this->getFields()[$name] instanceof $class)
        );
    }

    /**
     * Return the field with the given name.
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
            if ($field->getNative() === $nativeName && (\is_null($class) || ($field instanceof $class))) {
                return $field;
            }
        }

        throw new \Exception("The native field `$nativeName` does not exist");
    }

    /**
     * Return all fields.
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
     * Add a option to the resource.
     *
     * @param string|OptionElement $option
     * @return self
     */
    protected function addOption($option)
    {
        if (\is_string($option)) {
            $option = Option::get($option);
        }

        if (!$option->has('propagate') || $option->propagate !== false) {
            foreach ($this->getFields(AttributeField::class) as $attribute) {
                $attribute->addOption($option);
            }
        }

        return parent::addOption($option);
    }

    /**
     * Remove a option from the resource.
     *
     * @param  string|OptionElement $option
     * @return self
     */
    protected function removeOption($option)
    {
        if (\is_string($option)) {
            $option = Option::get($option);
        }

        foreach ($this->getFields(AttributeField::class) as $attribute) {
            $attribute->removeOption($option);
        }

        return parent::removeOption($option);
    }

    /**
     * Callaback when the instance is owned.
     *
     * @return void
     */
    protected function owned()
    {
        parent::owned();

        $this->ownFields();
    }

    /**
     * Own each fields.
     *
     * @return void
     */
    protected function ownFields()
    {
        foreach ($this->fields as $key => $field) {
            $name = $this->replaceInFieldTemplate($this->templates[$key], $key);

            $this->fields[$key] = $field->own($this, $name);

            $field->getMeta()->setField($field->getName(), $field);
        }
    }

    /**
     * Replace in field template
     *
     * @param string $template
     * @param string $fieldname
     * @return string
     */
    protected function replaceInFieldTemplate(string $template, string $fieldname)
    {
        $keyValues = [
            'modelname' => static::parseName($this->getMeta()->getModelClassName()),
            'identifier' => $fieldname,
            'name' => $this->getName(),
        ];

        return Str::replaceInTemplate($template, $keyValues);
    }

    /**
     * Each class locks in a specific way.
     *
     * @return void
     */
    protected function locking()
    {
        if (\count($this->fields) === 0) {
            throw new \Exception('A composed field needs at least one field');
        }

        $this->lockFields();

        parent::locking();
    }

    /**
     * Lock each sub fields.
     *
     * @return void
     */
    protected function lockFields()
    {
        foreach ($this->fields as $field) {
            $field->lock();
        }
    }

    /**
     * Return the has value for a specific field.
     *
     * @param Field                            $field
     * @param LaramoreModel|array|\ArrayAccess $model
     * @return mixed
     */
    public function hasFieldValue(Field $field, $model)
    {
        return $this->getOwner()->hasFieldValue($field, $model);
    }

    /**
     * Return the get value for a specific field.
     *
     * @param Field                            $field
     * @param LaramoreModel|array|\ArrayAccess $model
     * @return mixed
     */
    public function getFieldValue(Field $field, $model)
    {
        return $this->getOwner()->getFieldValue($field, $model);
    }

    /**
     * Return the set value for a specific field.
     *
     * @param Field                            $field
     * @param LaramoreModel|array|\ArrayAccess $model
     * @param mixed                            $value
     * @return mixed
     */
    public function setFieldValue(Field $field, $model, $value)
    {
        return $this->getOwner()->setFieldValue($field, $model, $value);
    }

    /**
     * Reset the value with the default value for a specific field.
     *
     * @param Field                            $field
     * @param LaramoreModel|array|\ArrayAccess $model
     * @return mixed
     */
    public function resetFieldValue(Field $field, $model)
    {
        return $this->getOwner()->resetFieldValue($field, $model);
    }

    /**
     * Return the get value for a relation field.
     *
     * @param  ExtraField                       $field
     * @param LaramoreModel|array|\ArrayAccess $model
     * @return mixed
     */
    public function retrieveFieldValue(ExtraField $field, $model)
    {
        return $this->getOwner()->retrieveFieldValue($field, $model);
    }

    /**
     * Return the get value for a relation field.
     *
     * @param RelationField $field
     * @param LaramoreModel $model
     * @return mixed
     */
    public function relateFieldValue(RelationField $field, LaramoreModel $model)
    {
        return $this->getOwner()->relateFieldValue($field, $model);
    }

    /**
     * Reverbate a saved relation value for a specific field.
     *
     * @param RelationField $field
     * @param LaramoreModel $model
     * @param mixed         $value
     * @return mixed
     */
    public function reverbateFieldValue(RelationField $field, LaramoreModel $model, $value)
    {
        return $this->getOwner()->reverbateFieldValue($field, $model, $value);
    }

    /**
     * Return generally a Builder after adding to it a condition.
     *
     * @param Field                $field
     * @param Proxied              $builder
     * @param Operator|string|null $operator
     * @param mixed                $value
     * @param mixed                ...$args
     * @return mixed
     */
    public function whereFieldValue(Field $field, Proxied $builder, $operator, $value=null, ...$args)
    {
        if (func_num_args() === 3) {
            return $this->getOwner()->whereFieldValue($field, $builder, $operator);
        }

        return $this->getOwner()->whereFieldValue($field, $builder, $operator, $value, ...$args);
    }

    /**
     * Serialize a value for a specific field.
     *
     * @param Field $field
     * @param mixed $value
     * @return mixed
     */
    public function serializeFieldValue(Field $field, $value)
    {
        return $this->getOwner()->serializeFieldValue($field, $value);
    }

    /**
     * Check if the value is correct for a specific field.
     *
     * @param Field $field
     * @param mixed $value
     * @return mixed
     */
    public function checkFieldValue(Field $field, $value)
    {
        return $this->getOwner()->checkFieldValue($field, $value);
    }

    /**
     * Dry a value for a specific field.
     *
     * @param Field $field
     * @param mixed $value
     * @return mixed
     */
    public function dryFieldValue(Field $field, $value)
    {
        return $this->getOwner()->dryFieldValue($field, $value);
    }

    /**
     * Hydrate a value for a specific field.
     *
     * @param Field $field
     * @param mixed $value
     * @return mixed
     */
    public function hydrateFieldValue(Field $field, $value)
    {
        return $this->getOwner()->hydrateFieldValue($field, $value);
    }

    /**
     * Cast a value for a specific field.
     *
     * @param Field $field
     * @param mixed $value
     * @return mixed
     */
    public function castFieldValue(Field $field, $value)
    {
        return $this->getOwner()->castFieldValue($field, $value);
    }

    /**
     * Call a field field method that is not basic.
     *
     * @param  Field  $field
     * @param  string $methodName
     * @param  array  $args
     * @return mixed
     */
    public function callFieldValueMethod(Field $field, string $methodName, array $args)
    {
        return $this->getOwner()->callFieldValueMethod($field, $methodName, $args);
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

        return parent::__call($method, $args);
    }
}
