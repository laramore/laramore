<?php
/**
 * Define a boolean field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

use Laramore\Facades\TypeManager;
use Illuminate\Database\Eloquent\Model;
use Laramore\Type;

class Boolean extends Field
{
    /**
     * Return the type object of the field.
     *
     * @return Type
     */
    public function getType(): Type
    {
        return TypeManager::boolean();
    }

    /**
     * Return the casted value for a specific model object.
     *
     * @param  Model $model
     * @param  mixed $value
     * @return mixed
     */
    public function castValue(Model $model, $value)
    {
        return is_null($value) ? $value : (bool) $value;
    }

    /**
     * Return if the value is true or false as expected.
     *
     * @param  Model   $model
     * @param  mixed   $value
     * @param  boolean $expected
     * @return boolean
     */
    public function isValue(Model $model, $value, bool $expected=true): bool
    {
        return $value == $expected;
    }
}
