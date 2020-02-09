<?php
/**
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */
namespace Laramore\Traits\Meta;
use Illuminate\Support\{
    Str, Collection
};
use Laramore\Fields\BaseField;
use Laramore\Facades\Operator;
use Laramore\Interfaces\{
	IsProxied, IsARelationField, IsALaramoreModel
};
trait HasFields
{
    /**
     * Return the get value for a specific field.
     *
     * @param BaseField        $field
     * @param IsALaramoreModel $model
     * @return mixed
     */
    public function getValueFieldAttribute(BaseField $field, IsALaramoreModel $model)
    {
        if ($field instanceof IsARelationField) {
            return $model->getRelationValue($field->name);
        }

        return ($model->getRawAttribute($field->attname) ?? null);
    }

    /**
     * Return the set value for a specific field.
     *
     * @param BaseField        $field
     * @param IsALaramoreModel $model
     * @param mixed            $value
     * @return mixed
     */
    public function setValueFieldAttribute(BaseField $field, IsALaramoreModel $model, $value)
    {
        if ($field instanceof IsARelationField) {
            return $model->setRelationValue($field->name, $value);
        }

        $owner = $field->getOwner();
        $value = $owner->transformFieldAttribute($field, $value);

        return $model->setRawAttribute($field->attname, $value);
    }

    /**
     * Reset the value with the default value for a specific field.
     *
     * @param BaseField        $field
     * @param IsALaramoreModel $model
     * @return mixed
     */
    public function resetValueFieldAttribute(BaseField $field, IsALaramoreModel $model)
    {
        return $model->setRawAttribute($field->getNative(), $field->getOwner()->defaultFieldAttribute($field));
    }

    /**
     * Return the get value for a relation field.
     *
     * @param IsARelationField $field
     * @param IsALaramoreModel $model
     * @return mixed
     */
    public function getRelationFieldAttribute(IsARelationField $field, IsALaramoreModel $model)
    {
        return $field->retrieve($model);
    }

    /**
     * Return the set value for a relation field.
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
        $value = $field->consume($model, $value);
        $model->setRawRelationValue($field->name, $value);

        return $value;
    }

    /**
     * Reverbate a saved relation value for a specific field.
     *
     * @param IsARelationField $field
     * @param IsALaramoreModel $model
     * @param mixed            $value
     * @return boolean
     */
    public function reverbateRelationFieldAttribute(IsARelationField $field, IsALaramoreModel $model, $value): bool
    {
        return $field->reverbate($model, $value);
    }

    /**
     * Return generally a Builder after adding to it a condition.
     *
     * @param BaseField            $field
     * @param IsProxied            $builder
     * @param Operator|string|null $operator
     * @param mixed                $value
     * @param mixed                ...$args
     * @return mixed
     */
    public function whereFieldAttribute(BaseField $field, IsProxied $builder, $operator=null, $value=null, ...$args)
    {
        if (func_num_args() === 2) {
            throw new \BadMethodCallException('Missing params');
        }

        if (func_num_args() === 3) {
            [$operator, $value] = [Operator::equal(), $operator];
        }

        if (!($operator instanceof Operator)) {
            $operator = Operator::find($operator ?: null);
        }

        if ($builder instanceof IsALaramoreModel) {
            $builder = $builder->newModelQuery();
        }

        switch ($operator->needs) {
            case 'null':
                $dryValue = null;
                break;

            case 'binary':
                $dryValue = (integer) $value;
                break;

            case 'collection':
                if (!($value instanceof Collection)) {
                    $value = collect($value);
                }

            default:
                if ($value instanceof Collection) {
                    $dryValue = $value->map(function ($sub) use ($field) {
                        return $field->getOwner()->dryFieldAttribute($field, $sub);
                    });
                } else {
                    $dryValue = $field->getOwner()->dryFieldAttribute($field, $value);
                }
                break;
        }

        if (\method_exists($field, $method = 'where'.Str::studly($operator->name))) {
            return \call_user_func([$field, $method], $builder, $dryValue, ...$args) ?: $builder;
        }

        if (!\in_array($operator->native, $builder->getQuery()->operators)) {
            throw new \LogicException('As the operator is not handled by default by Laravel, \
				you need to define a where method for this operator.');
        }

        return $field->where($builder, $operator, $dryValue, ...$args) ?: $builder;
    }

    /**
     * Return the query with this field as condition.
     *
     * @param BaseField $field
     * @param IsProxied $model
     * @return mixed
     */
    public function relateFieldAttribute(BaseField $field, IsProxied $model)
    {
        return $field->relate($model);
    }

    /**
     * Transform a value for a specific field.
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
     * Serialize a value for a specific field.
     *
     * @param BaseField $field
     * @param mixed     $value
     * @return mixed
     */
    public function serializeFieldAttribute(BaseField $field, $value)
    {
        return $field->serialize($value);
    }

    /**
     * Dry a value for a specific field.
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
     * Cast a value for a specific field.
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
     * Return the default value for a specific field.
     *
     * @param BaseField $field
     * @return mixed
     */
    public function defaultFieldAttribute(BaseField $field)
    {
        return $field->getProperty('default');
    }

    /**
     * Call a field attribute method that is not basic.
     *
     * @param  BaseField $field
     * @param  string    $methodName
     * @param  array     $args
     * @return mixed
     */
    public function callFieldAttributeMethod(BaseField $field, string $methodName, array $args)
    {
        return \call_user_func([$field, $methodName], ...$args);
    }
}
