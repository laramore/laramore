<?php
/**
 * Extra field contract.
 * Usefull field to interact with models but corresponds
 * to no attribute field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Contracts\Field;

use Laramore\Contracts\Eloquent\LaramoreModel;

interface ExtraField extends Field
{
    /**
    * Resolve extra value.
    *
    * @param LaramoreModel|array|\ArrayAccess $model
    * @return mixed
    */
   public function resolve($model);
}
