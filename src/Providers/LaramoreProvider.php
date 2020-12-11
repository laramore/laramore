<?php
/**
 * Add required macros and all base for Laramore.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Providers;

use Illuminate\Support\{
    ServiceProvider, Str
};
use Laramore\Elements\OperatorManager;
use Laramore\Elements\OptionManager;
use Laramore\Eloquent\MetaManager;
use Laramore\Facades\{
    FieldConstraint, Operator, Meta, Option
};
use Laramore\Fields\Constraint\ConstraintManager;
use Laramore\Traits\Provider\MergesConfig;

class LaramoreProvider extends ServiceProvider
{
    use MergesConfig;

    const CONFIG_TO_MERGE = [
        'option/elements.php' => 'option.elements',
        'operator/elements.php' => 'operator.elements',
        'field/properties.php' => 'field.properties',
    ];

    const CONFIG_TO_PUBLISH = [
        'option/elements.php',
        'operator/elements.php',
        'field/properties.php',
    ];

    protected static $metaManager;

    /**
     * During booting, add our macro.
     *
     * @return void
     */
    public function register()
    {
        foreach (static::CONFIG_TO_MERGE as $file => $config) {
            $this->mergeConfigFrom(
                __DIR__.'/../../config/'.$file, $config,
            );
        }

        $this->setSingletons();

        $this->booting(function () {
            $this->addStrMethod();
        });

        $this->booted(function () {
            $this->lockManagers();
        });
    }

    /**
     * Define all Laramore singletons.
     *
     * @return void
     */
    public function setSingletons()
    {
        $this->app->singleton('field_constraint', function() {
            return static::generateConstraintManager();
        });

        $this->app->singleton('operator', function() {
            return static::generateOperatorManager();
        });

        $this->app->singleton('meta', function() {
            if (!isset(static::$metaManager)) {
                return static::generateMetaManager()
                    ->setPreparing();
            }

            return static::$metaManager;
        });

        $this->app->singleton('option', function() {
            return static::generateOptionManager();
        });
    }

    /**
     * Publish the config linked to fields.
     *
     * @return void
     */
    public function boot()
    {
        $toPublish = [];

        foreach (static::CONFIG_TO_PUBLISH as $file) {
            $toPublish[__DIR__.'/../../config/'.$file] = config_path('/'.$file);
        }

        $this->publishes($toPublish);

        Meta::setPrepared();
    }

    /**
     * Generate option manager.
     *
     * @return OptionManager
     */
    public static function generateOptionManager(): OptionManager
    {
        $manager = new OptionManager();

        return $manager;
    }

    /**
     * Generate meta manager.
     *
     * @return MetaManager
     */
    public static function generateMetaManager(): MetaManager
    {
        static::$metaManager = new MetaManager;

        return static::$metaManager;
    }

    /**
     * Generate field constraint manager.
     *
     * @return ConstraintManager
     */
    public static function generateConstraintManager(): ConstraintManager
    {
        return new ConstraintManager();
    }

    /**
     * Generate operator manager.
     *
     * @return OperatorManager
     */
    public static function generateOperatorManager(): OperatorManager
    {
        return new OperatorManager();
    }

    /**
     * Add macro.
     *
     * @return void
     */
    public function addStrMethod()
    {
        Str::macro('replaceInTemplate', function (string $template, array $keyValues): string
        {
            foreach ($keyValues as $key => $value) {
                $template = \str_replace([
                    '${'.$key.'}', '#{'.$key.'}', '+{'.$key.'}', '_{'.$key.'}', '-{'.$key.'}',
                    '$^{'.$key.'}', '#^{'.$key.'}', '+^{'.$key.'}', '_^{'.$key.'}', '-^{'.$key.'}',
                ], [
                    $value, Str::singular($value), Str::plural($value), Str::snake($value), Str::camel($value),
                    \ucwords($value), Str::singular(\ucwords($value)), Str::plural(\ucwords($value)),
                    \ucwords(Str::snake($value)), \ucwords(Str::camel($value))
                ], $template);
            }

            return $template;
        });
    }

    /**
     * Lock all managers after booting.
     *
     * @return void
     */
    public function lockManagers()
    {
        Option::lock();
        Operator::lock();
        Meta::lock();
        FieldConstraint::lock();
    }
}
