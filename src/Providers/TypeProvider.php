<?php
/**
 * Load and prepare type manager.
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
use Laramore\Facades\Type;

class TypeProvider extends ServiceProvider implements LaramoreProvider
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
            __DIR__.'/../../config/type.php', 'type',
        );

        $this->app->singleton('type', function() {
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
            __DIR__.'/../../config/type.php' => config_path('type.php'),
        ]);
    }

    /**
     * Return the default values for the manager of this provider.
     *
     * @return array
     */
    public static function getDefaults(): array
    {
        return \array_filter(config('type.configurations'));
    }

    /**
     * Generate the corresponded manager.
     *
     * @return LaramoreManager
     */
    public static function generateManager(): LaramoreManager
    {
        $class = config('type.manager');

        $manager = new $class();
        $manager->set(static::getDefaults());
        $manager->define('default_options', ['visible', 'fillable', 'required']);

        return $manager;
    }

    /**
     * Lock all managers after booting.
     *
     * @return void
     */
    public function booted()
    {
        Type::lock();
    }
}
