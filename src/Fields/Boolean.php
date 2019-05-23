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
use Laramore\Type;

class Boolean extends Field
{
    public function getType(): Type
    {
        return TypeManager::boolean();
    }

    public function castValue($model, $value)
    {
        return is_null($value) ? $value : (bool) $value;
    }

    public function isValue($model, $value, $boolean=true)
    {
        return $value === $boolean;
    }
}
