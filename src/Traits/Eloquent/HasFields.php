<?php
/**
 * Metas manage fields.-white
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Traits\Eloquent;

use Illuminate\Support\Arr;
use Laramore\Facades\Operator;
use Laramore\Contracts\{
	Field\Field, Field\RelationField, Eloquent\LaramoreModel, Eloquent\LaramoreBuilder,
};
use Laramore\Contracts\Field\AttributeField;
use Laramore\Elements\OperatorElement;

trait HasFields
{
    /**
     * Return the has value for a specific field.
     *
     * @param Field                            $field
     * @param LaramoreModel|array|\ArrayAccess $model
     * @return mixed
     */
    public function hasFieldValue(Field $field, $model)
    {
        if ($model instanceof LaramoreModel) {
            if ($field instanceof AttributeField) {
                return $model->hasAttributeValue($field->getName());
            }

            if ($field instanceof RelationField) {
                return $model->hasRelationValue($field->getName());
            }

            return $model->hasExtraValue($field->getName());
        }

        if (\is_array($model) || ($model instanceof \ArrayAccess)) {
            if (\is_object($model) || Arr::isAssoc($model)) {
                return isset($model[$field->getName()]);
            }
        }

        return false;
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
        if ($model instanceof LaramoreModel) {
            if ($field instanceof AttributeField) {
                return $model->getAttributeValue($field->getName());
            }

            if ($field instanceof RelationField) {
                return $model->getRelationValue($field->getName());
            }

            return $model->getExtraValue($field->getName());
        }

        if (\is_array($model) || ($model instanceof \ArrayAccess)) {
            if (\is_object($model) || Arr::isAssoc($model)) {
                return $model[$field->getName()];
            } else if (isset($model[0])) {
                return $model[0];
            }
        }
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
        if ($field instanceof RelationField) {
            $field->reverbate($model, $value);
        }

        if ($model instanceof LaramoreModel) {
            if ($field instanceof AttributeField) {
                return $model->setAttributeValue($field->getName(), $value);
            }

            if ($field instanceof RelationField) {
                return $model->setRelationValue($field->getName(), $value);
            }

            return $model->setExtraValue($field->getName(), $value);
        }

        if (\is_array($model) || ($model instanceof \ArrayAccess)) {
            if (\is_object($model) || Arr::isAssoc($model)) {
                return $model[$field->getName()] = $value;
            }
        }
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
        if ($field->hasDefault()) {
            return $field->set($model, $field->getDefault());
        }

        if ($model instanceof LaramoreModel) {
            if ($field instanceof AttributeField) {
                return $model->unsetAttribute($field->getName());
            }

            if ($field instanceof RelationField) {
                return $model->unsetRelation($field->getName());
            }

            return $model->unsetExtra($field->getName());
        }

        if (\is_array($model) || ($model instanceof \ArrayAccess)) {
            if (\is_object($model) || Arr::isAssoc($model)) {
                unset($model[$field->getName()]);
            }
        }
    }

    /**
     * Sanitize value for a field.
     *
     * @param Field                       $field
     * @param LaramoreModel|array|\ArrayAccess $model
     * @param mixed                            $value
     * @return mixed
     */
    public function sanitizeFieldValue(Field $field, $model, $value)
    {
        if ($model instanceof LaramoreModel) {
            return $model->fetchingDatabase
                ? $field->hydrate($value)
                : $field->cast($value);
        }
    }

    /**
     * Retrieve value for a field.
     *
     * @param Field                       $field
     * @param LaramoreModel|array|\ArrayAccess $model
     * @return mixed
     */
    public function retrieveFieldValue(Field $field, $model)
    {
        if ($field instanceof AttributeField) {
            if (! ($model instanceof LaramoreModel) || ! $model->hasAttributeValue('id')) return;

            // TODO: Must be improved
            return $field->get(
                $field->getModel()::find($model->id, [$field->getNative()])
            );
        }

        if ($field instanceof RelationField) {
            if ($model instanceof LaramoreModel) {
                return $field->relate($model)->getResults();
            }

            return $field->getDefault();
        }

        $field->resolve($model);
    }

    /**
     * Return generally a Builder after adding to it a condition.
     *
     * @param Field                $field
     * @param LaramoreBuilder      $builder
     * @param Operator|string|null $operator
     * @param mixed                $value
     * @param mixed                ...$params
     * @return mixed
     */
    public function whereFieldValue(Field $field, LaramoreBuilder $builder, OperatorElement $operator, $value=null, ...$params)
    {
        if ($field instanceof AttributeField) {
            \call_user_func([$builder->getQuery(), 'where'], $field->getQualifiedName(), $operator, $value, ...$params);
        }

        return $builder;
    }

    /**
     * Call a field attribute method that is not basic.
     *
     * @param  Field  $field
     * @param  string $methodName
     * @param  array  $args
     * @return mixed
     */
    public function callFieldValueMethod(Field $field, string $methodName, array $args)
    {
        return \call_user_func([$field, $methodName], ...$args);
    }
}
