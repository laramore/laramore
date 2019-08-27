<?php
/**
 * Use the Laramore engine with the Eloquent pivot.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore;

use Laramore\Traits\Model\HasLaramore;
use Illuminate\Database\Eloquent\Relations\Pivot as BasePivot;
use Laramore\PivotMeta;

abstract class Pivot extends BasePivot
{
    use HasLaramore;

    /**
     * Return the meta class to use.
     *
     * @return string
     */
    public static function getMetaClass(): string
    {
        return PivotMeta::class;
    }
}
