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

    public function isValue(Model $model, $value, $boolean=true)
    {
        return $value === $boolean;
    }
}
