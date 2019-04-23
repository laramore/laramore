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

interface IsAFieldOwner
{
    public function setFieldValue($model, $field, $value);

    public function getFieldValue($model, $field, $value);
}
