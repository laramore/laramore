<?php
/**
 * Load and prepare option manager.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Providers;

use Illuminate\Support\ServiceProvider;
use Laramore\Traits\Provider\MergesConfig;
use Laramore\Contracts\{
    Manager\LaramoreManager, Provider\LaramoreProvider
};
use Laramore\Facades\Option;

class OptionProvider extends ServiceProvider implements LaramoreProvider
{
    use MergesConfig;

    /**
     * Register our facade and create the manager.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/option.php', 'option',
        );

        $this->app->singleton('option', function() {
            return static::generateManager();
        });

        $this->app->booted([$this, 'booted']);
    }

    /**
     * Publish the config linked to the manager.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/option.php' => config_path('option.php'),
        ]);
    }

    /**
     * Return the default values for the manager of this provider.
     *
     * @return array
     */
    public static function getDefaults(): array
    {
        return \array_filter(config('option.configurations'));
    }

    /**
     * Generate the corresponded manager.
     *
     * @return LaramoreManager
     */
    public static function generateManager(): LaramoreManager
    {
        $class = config('option.manager');

        $manager = new $class();
        $manager->set(static::getDefaults());
        $manager->define('adds', []);
        $manager->define('removes', []);

        return $manager;
    }

    /**
     * Lock all managers after booting.
     *
     * @return void
     */
    public function booted()
    {
        Option::lock();
    }
}
