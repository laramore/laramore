<?php
/**
 * Many relation field contract.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Contracts\Field;

use Laramore\Contracts\Eloquent\LaramoreModel;

interface ManyRelationField extends RelationField
{
    /**
     * Cast the value to be used as a correct model.
     *
     * @param  mixed $value
     * @return LaramoreModel
     */
    public function castModel($value);
}
