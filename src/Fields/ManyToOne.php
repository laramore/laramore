<?php
/**
 * Define a foreign field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

use Laramore\Contracts\{
    Eloquent\LaramoreModel, Field\RelationField
};
use Laramore\Traits\Field\ToOneRelation;

class ManyToOne extends BaseComposed implements RelationField
{
    use ToOneRelation;

    /**
     * On update action.
     *
     * @var string
     */
    protected $onUpdate;

    /**
     * On delete action.
     *
     * @var string
     */
    protected $onDelete;

    public const CASCADE = 'cascade';
    public const RESTRICT = 'restrict';
    public const SET_NULL = 'set null';
    public const SET_DEFAULT = 'set default';

    /**
     * Reet the value for the field.
     *
     * @param LaramoreModel|array|\ArrayAccess $model
     * @return mixed
     */
    public function reset($model)
    {
        if ($model instanceof LaramoreModel) {
            parent::reset($model);
        }

        $this->getField('id')->reset($model);
    }
}
