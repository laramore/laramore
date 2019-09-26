<?php
/**
 * Owner interface.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Interfaces;

use Laramore\Builder;
use Laramore\Fields\BaseField;
use Laramore\Interfaces\{
    IsProxied, IsALaramoreModel, IsARelationField
};

interface IsAFieldOwner
{
    /**
     * Return the get value for a specific field.
     *
     * @param BaseField $field
     * @param IsALaramoreModel     $model
     * @param mixed     $value
     * @return mixed
     */
    public function getFieldAttribute(BaseField $field, IsALaramoreModel $model);

    /**
     * Return the set value for a specific field.
     *
     * @param BaseField $field
     * @param IsALaramoreModel     $model
     * @param mixed     $value
     * @return mixed
     */
    public function setFieldAttribute(BaseField $field, IsALaramoreModel $model, $value);

    /**
     * Return the set value for a specific field.
     *
     * @param IsALaramoreModel     $model
     * @param BaseField $field
     * @param mixed     $value
     * @return mixed
     */
    public function resetFieldAttribute(BaseField $field, IsALaramoreModel $model);

    /**
     * Return the set value for a specific field.
     *
     * @param BaseField $field
     * @param mixed     $value
     * @return mixed
     */
    public function dryFieldAttribute(BaseField $field, $value);

    /**
     * Return the set value for a specific field.
     *
     * @param BaseField $field
     * @param mixed     $value
     * @return mixed
     */
    public function castFieldAttribute(BaseField $field, $value);

    /**
     * Return the set value for a specific field.
     *
     * @param BaseField $field
     * @return mixed
     */
    public function defaultFieldAttribute(BaseField $field);

    /**
     * Return the set value for a specific field.
     *
     * @param BaseField $field
     * @return mixed
     */
    public function callFieldAttributeMethod(BaseField $field, string $methodName, array $args);
}
