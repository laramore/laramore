<?php
/**
 * Load and prepare operator manager.
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
use Laramore\Elements\OperatorElement;
use Laramore\Facades\Operator;

class OperatorProvider extends ServiceProvider implements LaramoreProvider
{
    use MergesConfig;

    /**
     * Create the OperatorManager and lock it after booting.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/operator.php', 'operator',
        );

        $this->app->singleton('operator', function() {
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
            __DIR__.'/../../config/operator.php' => config_path('operator.php'),
        ]);
    }

    /**
     * Return the default values for the manager of this provider.
     *
     * @return array
     */
    public static function getDefaults(): array
    {
        return \array_filter(config('operator.configurations'));
    }

    /**
     * Generate the corresponded manager.
     *
     * @return LaramoreManager
     */
    public static function generateManager(): LaramoreManager
    {
        $class = config('operator.manager');

        $manager = new $class();
        $manager->set(static::getDefaults());
        $manager->define('value_type', OperatorElement::MIXED_TYPE);
        $manager->define('fallback', '=');

        return $manager;
    }

    /**
     * Lock all managers after booting.
     *
     * @return void
     */
    public function booted()
    {
        Operator::lock();
    }
}
