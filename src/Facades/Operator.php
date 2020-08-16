<?php
/**
 * Add a facade for the manager Operator.
 * Simple access to operators.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Facades;

use Illuminate\Support\Facades\Facade;

class Operator extends Facade
{
    /**
     * Give the name of the accessor.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'operator';
    }
}
