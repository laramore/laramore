<?php
/**
 * Inject in models auto fields and relations management.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Traits\Eloquent;

use Laramore\Eloquent\PivotMeta;


trait HasLaramorePivot
{
    use HasLaramoreModel {
        HasLaramoreModel::getMetaClass as protected getBaseMetaClass;
    }

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
