<?php
/**
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Traits\Meta;

use Illuminate\Database\Eloquent\Model;
use Laramore\Builder;
use Laramore\Fields\BaseField;
use Laramore\Interfaces\IsProxied;

trait HasFields
{
    /**
     * Return the get value for a specific field.
     *
     * @param BaseField $field
     * @param Model     $model
     * @param mixed     $value
     * @return mixed
     */
    public function getFieldAttribute(BaseField $field, Model $model)
    {
        return $model->getRawAttribute($field->attname);
    }

    /**
     * Return the set value for a specific field.
     *
     * @param BaseField $field
     * @param Model     $model
     * @param mixed     $value
     * @return mixed
     */
    public function setFieldAttribute(BaseField $field, Model $model, $value)
    {
        $owner = $field->getOwner();
        $value = $owner->transformFieldAttribute($field, $value);
        $owner->checkFieldAttribute($field, $value);

        return $model->setRawAttribute($field->attname, $value);
    }

    /**
     * Return the set value for a specific field.
     *
     * @param BaseField $field
     * @param Model     $model
     * @param mixed     $value
     * @return mixed
     */
    public function resetFieldAttribute(BaseField $field, Model $model)
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
        return call_user_func([$field, $methodName], ...$args);
    }
}
