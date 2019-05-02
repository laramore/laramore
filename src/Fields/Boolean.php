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

class Boolean extends Field
{
    public function getDefaultProperties(): array
    {
        return [
            'type' => 'boolean',
        ];
    }

    public function castValue($value)
    {
        return is_null($value) ? $value : (bool) $value;
    }

    public function isValue($model, $value, $boolean=true)
    {
        return $value === $boolean;
    }
}
