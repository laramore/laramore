<?php
/**
 * Define a reverse manytomany field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields\Reversed;

use Laramore\Contracts\{
    Field\ManyRelationField, Eloquent\LaramoreMeta, Field\RelationField
};
use Laramore\Traits\Field\{
    ReversedRelation, ManyToManyRelation
};
use Laramore\Fields\BaseField;

class BelongsToMany extends BaseField implements ManyRelationField
{
    use ManyToManyRelation, ReversedRelation;

    /**
     * Return the pivot meta.
     *
     * @return LaramoreMeta
     */
    public function getPivotMeta(): LaramoreMeta
    {
        return $this->getReversedField()->getPivotMeta();
    }

    /**
     * Return the pivot source.
     *
     * @return RelationField
     */
    public function getPivotSource(): RelationField
    {
        return $this->getReversedField()->getPivotTarget();
    }

    /**
     * Return the pivot target.
     *
     * @return RelationField
     */
    public function getPivotTarget(): RelationField
    {
        return $this->getReversedField()->getPivotSource();
    }
}
