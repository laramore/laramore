<?php
/**
 * Add a lock management.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Traits;

use Illuminate\Support\Traits\Macroable;
use Illuminate\Foundation\Application;
use Laramore\Exceptions\LockException;

trait HasLockedMacros
{
    use Macroable {
        Macroable::macro as protected macroFromTrait;
    }

    /**
     * Register a custom macro.
     *
     * @param  mixed    $name
     * @param  callable $macro
     * @return void
     */
    public static function macro($name, callable $macro)
    {
        if (Application::getInstance()->isBooted()) {
            throw new LockException('No more macros could be defined after the application booting', $name);
        }

        static::macroFromTrait($name, $macro);
    }
}
