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

use Illuminate\Support\Collection;
use Laramore\Facades\Operator;
use Laramore\Contracts\{
	Field\Field, Field\RelationField, Field\ExtraField, Eloquent\LaramoreModel, Eloquent\LaramoreBuilder,
};
use Laramore\Contracts\Field\AttributeField;
use Laramore\Elements\OperatorElement;

trait HasFields
{
    /**
     * Transform a value for a specific field.
     *
     * @param Field $field
     * @param mixed $value
     * @return mixed
     */
    public function transformFieldValue(Field $field, $value)
    {
        return $field->transform($value);
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
        return $field->serialize($value);
    }

    /**
     * Dry a value for a specific field.
     *
     * @param AttributeField $field
     * @param mixed          $value
     * @return mixed
     */
    public function dryFieldValue(AttributeField $field, $value)
    {
        return $field->dry($value);
    }

    /**
     * Hydrate a value for a specific field.
     *
     * @param AttributeField $field
     * @param mixed          $value
     * @return mixed
     */
    public function hydrateFieldValue(AttributeField $field, $value)
    {
        return $field->hydrate($value);
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
        return $field->cast($value);
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
        return $field->has($model);
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
        return $field->get($model);
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
        // Refuse any transformation and reverbation if the model is currently fetchingDatabase.
        if (!($model instanceof LaramoreModel)) {
            // Apply changes by the field.
            $value = $this->castFieldValue($field, $value);
        } else if (!$model->fetchingDatabase) {
            // Apply changes by the field.
            $value = $this->castFieldValue($field, $value);

            if ($field instanceof RelationField) {
                $value = $field->getOwner()->reverbateFieldValue($field, $model, $value);
            }
        } else if ($field instanceof AttributeField) {
            $value = $this->hydrateFieldValue($field, $value);
        }

        // Set the value in the model.
        return $field->set($model, $value);
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
        return $field->reset($model);
    }

    /**
     * Return the set value for a relation field.
     *
     * @param Field                       $field
     * @param LaramoreModel|array|\ArrayAccess $model
     * @return mixed
     */
    public function retrieveFieldValue(Field $field, $model)
    {
        return $field->retrieve($model);
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
        return $field->relate($model);
    }

    /**
     * Reverbate the relation value for a specific field.
     *
     * @param RelationField $field
     * @param LaramoreModel $model
     * @param mixed         $value
     * @return mixed
     */
    public function reverbateFieldValue(RelationField $field, LaramoreModel $model, $value)
    {
        return $field->reverbate($model, $value);
    }

    /**
     * Return generally a Builder after adding to it a condition.
     *
     * @param Field                $field
     * @param LaramoreBuilder      $builder
     * @param Operator|string|null $operator
     * @param mixed                $value
     * @param mixed                ...$args
     * @return mixed
     */
    public function whereFieldValue(Field $field, LaramoreBuilder $builder, $operator, $value=null, ...$args)
    {
        if (func_num_args() === 3) {
            [$operator, $value] = [Operator::equal(), $operator];
        }

        if (!($operator instanceof OperatorElement)) {
            $operator = Operator::find($operator ?: '=');
        }

        if ($builder instanceof LaramoreModel) {
            $builder = $builder->newModelQuery();
        }

        if ($operator->needs(OperatorElement::COLLECTION_TYPE) && !($value instanceof Collection)) {
            $value = new Collection($value);
        }

        if ($field instanceof AttributeField) {
            switch ($operator->valueType) {
                case OperatorElement::NULL_TYPE:
                    $value = null;
                    break;

                case OperatorElement::BINARY_TYPE:
                    $value = (integer) $value;
                    break;
            }
        }

        if ($operator->needs(OperatorElement::COLLECTION_TYPE)) {
            $value = $value->map(function ($sub) use ($field) {
                return $field->cast($sub);
            });
        } else {
            $value = $field->cast($value);
        }

        if (\method_exists($field, $method = $operator->getWhereMethod())) {
            if ($operator->needs(OperatorElement::NULL_TYPE)) {
                return \call_user_func([$field, $method], $builder, ...$args) ?: $builder;
            }

            return \call_user_func([$field, $method], $builder, $value, ...$args) ?: $builder;
        }

        if (!\in_array($operator->native, $builder->getQuery()->operators)) {
            throw new \LogicException("The operator {$operator->native} is not available for the field {$field->getName()}");
        }

        return $field->where($builder, $operator, $value, ...$args) ?: $builder;
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
