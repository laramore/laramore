<?php
/**
 * Add a facade for the Types.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Facades;

use Illuminate\Support\Facades\Facade;

class Types extends Facade
{
    /**
     * Give the name of the accessor.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Types';
    }
}
