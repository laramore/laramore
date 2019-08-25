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
     * Fake pivots are not instanciable as they are fake classes.
     *
     * @param mixed ...$args
     * @throws LaramoreException In any cases.
     */
    public function __construct(...$args)
    {
        throw new LaramoreException($this, 'You need to create your own pivot class, you cannot create a pivot from fake one');
    }

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
