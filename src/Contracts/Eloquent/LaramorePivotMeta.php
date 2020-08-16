<?php
/**
 * Laramore pivot meta.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Contracts\Eloquent;

use Laramore\Contracts\Field\{
    FieldsOwner, RelationField
};

interface LaramorePivotMeta extends FieldsOwner
{
    /**
     * Indicate the this meta is a pivot one.
     *
     * @return boolean
     */
    public function isPivot(): bool;

    /**
     * Return all foreign pivots.
     *
     * @return array
     */
    public function getPivots(): array;

    /**
     * Define pivots for this meta pivot.
     *
     * @param RelationField $pivotSource
     * @param RelationField $pivotTarget
     * @return self
     */
    public function pivots(RelationField $pivotSource, RelationField $pivotTarget);
}
