<?php
/**
 * Use the Laramore engine with the Eloquent pivot.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Eloquent;

use Laramore\Contracts\Eloquent\LaramoreMeta;

class FakePivot extends BasePivot
{
    /**
     * Allow the user to define all meta data for the current pivot.
     *
     * @param  LaramoreMeta $meta
     * @return mixed
     */
    public static function meta(LaramoreMeta $meta)
    {
        return $meta;
    }
}
