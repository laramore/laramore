<?php
/**
 * Fields owner contract.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Contracts\Field;

use Laramore\Contracts\{
    Eloquent\LaramoreBuilder, Eloquent\LaramoreModel, Field\Field, Field\AttributeField, Field\RelationField, Field\ExtraField
};

interface FieldsOwner
{
    /**
     * Indicate if a field with a given name exists.
     *
     * @param  string $name
     * @param  string $class The field must be an instance of the class.
     * @return boolean
     */
    public function hasField(string $name, string $class=null): bool;

    /**
     * Return a field with a given name.
     *
     * @param  string $name
     * @param  string $class The field must be an instance of the class.
     * @return Field
     */
    public function getField(string $name, string $class=null): Field;

    /**
     * Return a field with its native name.
     *
     * @param  string $nativeName
     * @param  string $class      The field must be an instance of the class.
     * @return Field
     */
    public function findField(string $nativeName, string $class=null): Field;

    /**
     * Define a field with a given name.
     *
     * @param string $name
     * @param Field  $field
     * @return self
     */
    public function setField(string $name, Field $field);

    /**
     * Return all fields.
     *
     * @param  string $class Each field must be an instance of the class.
     * @return array
     */
    public function getFields(string $class=null): array;

    /**
     * Return the has value for a specific field.
     *
     * @param Field                            $field
     * @param LaramoreModel|array|\ArrayAccess $model
     * @return mixed
     */
    public function hasFieldValue(Field $field, $model);

    /**
     * Return the get value for a specific field.
     *
     * @param Field                            $field
     * @param LaramoreModel|array|\ArrayAccess $model
     * @return mixed
     */
    public function getFieldValue(Field $field, $model);

    /**
     * Return the set value for a specific field.
     *
     * @param Field                            $field
     * @param LaramoreModel|array|\ArrayAccess $model
     * @param mixed                            $value
     * @return mixed
     */
    public function setFieldValue(Field $field, $model, $value);

    /**
     * Reset the value with the default value for a specific field.
     *
     * @param Field                            $field
     * @param LaramoreModel|array|\ArrayAccess $model
     * @return mixed
     */
    public function resetFieldValue(Field $field, $model);

    /**
     * Retrieve values from the relation field.
     *
     * @param ExtraField                       $field
     * @param LaramoreModel|array|\ArrayAccess $model
     * @return mixed
     */
    public function retrieveFieldValue(ExtraField $field, $model);

    /**
     * Return the get value for a relation field.
     *
     * @param RelationField $field
     * @param LaramoreModel $model
     * @return mixed
     */
    public function relateFieldValue(RelationField $field, LaramoreModel $model);

    /**
     * Reverbate the relation value for a specific field.
     *
     * @param RelationField $field
     * @param LaramoreModel $model
     * @param mixed         $value
     * @return mixed
     */
    public function reverbateFieldValue(RelationField $field, LaramoreModel $model, $value);

    /**
     * Return generally a Builder after adding to it a condition.
     *
     * @param Field                       $field
     * @param LaramoreBuilder             $builder
     * @param OperatorElement|string|null $operator
     * @param mixed                       $value
     * @param mixed                       ...$args
     * @return mixed
     */
    public function whereFieldValue(Field $field, LaramoreBuilder $builder, $operator, $value=null, ...$args);

    /**
     * Serialize a value for a specific field.
     *
     * @param Field $field
     * @param mixed $value
     * @return mixed
     */
    public function serializeFieldValue(Field $field, $value);

    /**
     * Dry a value for a specific field.
     *
     * @param AttributeField $field
     * @param mixed          $value
     * @return mixed
     */
    public function dryFieldValue(AttributeField $field, $value);

    /**
     * Hydrate a value for a specific field.
     *
     * @param AttributeField $field
     * @param mixed          $value
     * @return mixed
     */
    public function hydrateFieldValue(AttributeField $field, $value);

    /**
     * Cast a value for a specific field.
     *
     * @param Field $field
     * @param mixed $value
     * @return mixed
     */
    public function castFieldValue(Field $field, $value);

    /**
     * Call a field attribute method that is not basic.
     *
     * @param Field  $field
     * @param string $methodName
     * @param array  $args
     * @return mixed
     */
    public function callFieldValueMethod(Field $field, string $methodName, array $args);
}
