<?php
/**
 * Load and prepare constraints.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Providers;

use Illuminate\Support\ServiceProvider;
use Laramore\Contracts\{
	Manager\LaramoreManager, Provider\LaramoreProvider
};
use Laramore\Facades\FieldConstraint;

class FieldConstraintProvider extends ServiceProvider implements LaramoreProvider
{
    /**
     * Before booting, create our definition for migrations.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/field/constraint.php', 'field.constraint',
        );

        $this->app->singleton('field_constraint', function() {
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
            __DIR__.'/../../config/field/constraint.php' => config_path('/field/constraint.php'),
        ]);
    }

    /**
     * Return the default values for the manager of this provider.
     *
     * @return array
     */
    public static function getDefaults(): array
    {
        return config('field.constraint.configurations');
    }

    /**
     * Generate the corresponded manager.
     *
     * @return LaramoreManager
     */
    public static function generateManager(): LaramoreManager
    {
        $class = config('field.constraint.manager');

        return new $class(static::getDefaults());
    }

    /**
     * Lock all managers after booting.
     *
     * @return void
     */
    public function booted()
    {
        FieldConstraint::lock();
    }
}
