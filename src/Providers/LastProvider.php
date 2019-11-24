<?php
/**
 * Lock in first the Meta as the bootable method is launched at last.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Providers;

use Illuminate\Support\ServiceProvider;
use Laramore\Interfaces\{
	IsALaramoreManager, IsALaramoreProvider, IsALaramoreModel
};
use Laramore\Exceptions\ConfigException;
use Laramore\Traits\Provider\MergesConfig;
use Laramore\MetaManager;
use ReflectionNamespace;

class LastProvider extends ServiceProvider
{
    /**
     * Lock in first the Meta as the bootable method is launched at last.
     *
     * @return void
     */
    public function boot()
    {
        static::getManager()->lock();
    }

    /**
     * Return the meta manager.
     *
     * @return MetaManager
     */
    public static function getManager(): MetaManager
    {
        return MetasProvider::getManager();
    }
}
