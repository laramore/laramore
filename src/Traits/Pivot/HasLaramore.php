<?php
/**
 * Inject in models auto fields and relations management.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Traits\Pivot;

use Laramore\Traits\Model\HasLaramore as BaseHasLaramore;
use Laramore\PivotMeta;

trait HasLaramore
{
    use BaseHasLaramore {
        BaseHasLaramore::getMetaClass as protected getBaseMetaClass;
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
