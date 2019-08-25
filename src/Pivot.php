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
     * Generate one time the pivot meta.
     *
     * @return void
     */
    protected static function prepareMeta()
    {
        static::$meta = new PivotMeta(static::class);

        // Generate all meta data defined by the user in the current pivot.
        static::__meta(static::$meta);
    }
}
