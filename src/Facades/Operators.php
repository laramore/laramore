<?php
/**
 * Add a facade for the Operators.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Facades;

use Illuminate\Support\Facades\Facade;

class Operators extends Facade
{
    /**
     * Give the name of the accessor.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Operators';
    }
}
