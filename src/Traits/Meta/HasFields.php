<?php
/**
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Traits\Meta;

use Illuminate\Support\Str;
use Laramore\Builder;
use Laramore\Fields\{
	BaseField, Field
};
use Laramore\Interfaces\{
	IsProxied, IsARelationField, IsALaramoreModel
};
use Op;

trait HasFields
{
    /**
     * Return the get value for a specific field.
     *
     * @param BaseField        $field
     * @param IsALaramoreModel $model
     * @param mixed            $value
     * @return mixed
     */
    public function getFieldAttribute(BaseField $field, IsALaramoreModel $model)
    {
        if ($field instanceof IsARelationField) {
            return $model->getRelationValue($field->name);
        }

        return $model->getRawAttribute($field->attname) ?? null;
    }

    /**
     * Return the set value for a specific field.
     *
     * @param BaseField        $field
     * @param IsALaramoreModel $model
     * @param mixed            $value
     * @return mixed
     */
    public function setFieldAttribute(BaseField $field, IsALaramoreModel $model, $value)
    {
        if ($field instanceof IsARelationField) {
            return $model->setRelationValue($field->name, $value);
        }

        $owner = $field->getOwner()->checkFieldAttribute($field, $value);
        $value = $owner->transformFieldAttribute($field, $value);

        return $model->setRawAttribute($field->attname, $value);
    }

    /**
     * Return the get value for a specific field.
     *
     * @param IsARelationField $field
     * @param IsALaramoreModel $model
     * @param mixed            $value
     * @return mixed
     */
    public function getRelationFieldAttribute(IsARelationField $field, IsALaramoreModel $model)
    {
        return $field->retrieve($model);
    }

    /**
     * Return the set value for a specific field.
     *
     * @param IsARelationField $field
     * @param IsALaramoreModel $model
     * @param mixed            $value
     * @return mixed
     */
    public function setRelationFieldAttribute(IsARelationField $field, IsALaramoreModel $model, $value)
    {
        $owner = $field->getOwner();
        $value = $owner->transformFieldAttribute($field, $value);
        $owner->checkFieldAttribute($field, $value);

        $model->setRawRelationValue($field->name, $field->consume($model, $value));

        return $model;
    }

    /**
     * Return the set value for a specific field.
     *
     * @param BaseField        $field
     * @param IsALaramoreModel $model
     * @param mixed            $value
     * @return mixed
     */
    public function whereFieldAttribute(BaseField $field, IsProxied $builder, $operator=null, $value=null, ...$args)
    {
        if (func_num_args() === 2) {
            throw new \BadMethodCallException('Missing params');
        }

        if (func_num_args() === 3) {
            [$operator, $value] = [Op::equal(), $operator];
        }

        if (!($operator instanceof Operator)) {
            $operator = Op::find($operator ?: null);
        }

        if ($builder instanceof IsALaramoreModel) {
            $builder = $builder->newModelQuery();
        }

        switch ($operator->needs) {
            case 'null':
                $driedValue = null;
                break;

            case 'binary':
                $driedValue = (integer) $value;
                break;

            case 'collection':
                if (!($value instanceof Collection)) {
                          $value = collect($value);
                }

            default:
                if ($value instanceof Collection) {
                          $driedValue = $value->map(function ($sub) use ($field) {
                            return $field->getOwner()->dryFieldAttribute($field, $sub);
                          });
                } else {
                       $driedValue = $field->getOwner()->dryFieldAttribute($field, $value);
                }
                break;
        }

        if (\method_exists($field, $method = 'where'.Str::studly($operator->name))) {
            return \call_user_func([$field, $method], $builder, $driedValue, ...$args) ?: $builder;
        } else {
            if (!\in_array($operator->native, $builder->getQuery()->operators)) {
                throw new \LogicException('As the operator is not handled by default by Laravel, you need to define a where method for this operator.');
            }

            return $field->where($builder, $operator, $driedValue, ...$args) ?: $builder;
        }
    }

    /**
     * Return the set value for a specific field.
     *
     * @param BaseField        $field
     * @param IsALaramoreModel $model
     * @param mixed            $value
     * @return mixed
     */
    public function relateFieldAttribute(BaseField $field, IsProxied $model)
    {
        return $field->relate($model);
    }

    /**
     * Return the set value for a specific field.
     *
     * @param BaseField        $field
     * @param IsALaramoreModel $model
     * @param mixed            $value
     * @return mixed
     */
    public function resetFieldAttribute(BaseField $field, IsALaramoreModel $model)
    {
        return $model->setRawAttribute($field->attname, $field->getOwner()->defaultFieldAttribute($field));
    }

    /**
     * Return the set value for a specific field.
     *
     * @param BaseField $field
     * @param mixed     $value
     * @return mixed
     */
    public function transformFieldAttribute(BaseField $field, $value)
    {
        return $field->transform($value);
    }

    /**
     * Return the set value for a specific field.
     *
     * @param BaseField $field
     * @param mixed     $value
     * @return mixed
     */
    public function checkFieldAttribute(BaseField $field, $value)
    {
        return $field->check($value);
    }

    /**
     * Return the set value for a specific field.
     *
     * @param BaseField $field
     * @param mixed     $value
     * @return mixed
     */
    public function dryFieldAttribute(BaseField $field, $value)
    {
        return $field->dry($value);
    }

    /**
     * Return the set value for a specific field.
     *
     * @param BaseField $field
     * @param mixed     $value
     * @return mixed
     */
    public function castFieldAttribute(BaseField $field, $value)
    {
        return $field->cast($value);
    }

    /**
     * Return the set value for a specific field.
     *
     * @param BaseField $field
     * @return mixed
     */
    public function defaultFieldAttribute(BaseField $field)
    {
        return $field->getProperty('default', false);
    }

    public function callFieldAttributeMethod(BaseField $field, string $methodName, array $args)
    {
        return \call_user_func([$field, $methodName], ...$args);
    }
}
