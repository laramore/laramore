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

use Laramore\Exceptions\LaramoreException;

class FakePivot extends Pivot
{
    /**
     * Allow the user to define all meta data for the current pivot.
     *
     * @param  Meta $meta
     * @return void
     */
    protected static function __meta(Meta $meta)
    {
        return $meta;
    }
}
