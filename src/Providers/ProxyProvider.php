<?php
/**
 * Load and prepare proxies.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Providers;

use Illuminate\Support\ServiceProvider;
use Laramore\Contracts\{
	Manager\LaramoreManager, Provider\LaramoreProvider
};
use Laramore\Facades\Proxy;

class ProxyProvider extends ServiceProvider implements LaramoreProvider
{
    /**
     * Field manager.
     *
     * @var array
     */
    protected static $managers;

    /**
     * Before booting, create our definition for migrations.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/proxy.php', 'proxy',
        );

        $this->app->singleton('proxy', function() {
            return static::generateManager();
        });

        $this->app->booted([$this, 'booted']);
    }

    /**
     * Publish the config linked to fields.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/proxy.php' => config_path('/proxy.php'),
        ]);
    }

    /**
     * Return the default values for the manager of this provider.
     *
     * @return array
     */
    public static function getDefaults(): array
    {
        return config('proxy.configurations');
    }

    /**
     * Generate the corresponded manager.
     *
     * @return LaramoreManager
     */
    public static function generateManager(): LaramoreManager
    {
        $class = config('proxy.manager');

        return new $class(static::getDefaults());
    }

    /**
     * Lock all managers after booting.
     *
     * @return void
     */
    public function booted()
    {
        Proxy::lock();
    }
}
