<?php
/**
 * Define a one to one field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

use Laramore\Contracts\Field\Constraint\UniqueField;
use Laramore\Traits\Field\ToOneRelation;
use Laramore\Contracts\Field\RelationField;

class OneToOne extends BaseComposed implements RelationField
{
    use ToOneRelation;

    /**
     * This composed field needs to have a unique id field.
     *
     * @return void
     */
    public function locking()
    {
        parent::locking();

        if (!($this->getField('id') instanceof UniqueField)) {
            throw new \LogicException('The field defining the unique relation must implement `UniqueField`');
        }
    }
}
