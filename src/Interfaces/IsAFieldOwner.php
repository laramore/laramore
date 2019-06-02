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

use Illuminate\Database\Eloquent\Model;
use Laramore\Fields\BaseField;

interface IsAFieldOwner
{
    public function setFieldValue(Model $model, BaseField $field, $value);

    public function getFieldValue(Model $model, BaseField $field, $value);
}
