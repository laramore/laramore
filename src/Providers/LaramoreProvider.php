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
use Laramore\Exceptions\ConfigException;
use Laramore\Contracts\{
    Manager\LaramoreManager, Eloquent\LaramoreModel
};
use Laramore\Elements\OperatorElement;
use Laramore\Facades\{
    FieldConstraint, Operator, Type, Meta, Proxy, Option
};
use ReflectionNamespace, ReflectionClass;
    
class LaramoreProvider extends ServiceProvider
{
    const CONFIG_TO_MERGE = [
        'meta.php' => 'meta',
        'option.php' => 'option',
        'operator.php' => 'operator',
        'type.php' => 'type',
        'field.php' => 'field',
        'field/constraint.php' => 'field.constraint',
        'field/proxy.php' => 'field.proxy',
        'proxy.php' => 'proxy',
    ];

    const CONFIG_TO_PUBLISH = [
        'meta.php',
        'option.php',
        'operator.php',
        'type.php',
        'field.php',
        'field/constraint.php',
        'field/proxy.php',
        'proxy.php',
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
                return static::generateMetaManager();
            }

            return static::$metaManager;
        });

        $this->app->singleton('option', function() {
            return static::generateOptionManager();
        });

        $this->app->singleton('proxy', function() {
            return static::generateProxyManager();
        });

        $this->app->singleton('type', function() {
            return static::generateTypeManager();
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
     * Return the default values for the manager of this provider.
     *
     * @return array
     */
    public static function getOptionDefaults(): array
    {
        return \array_filter(config('option.configurations'));
    }

    /**
     * Generate the corresponded manager.
     *
     * @return LaramoreManager
     */
    public static function generateOptionManager(): LaramoreManager
    {
        $class = config('option.manager');

        $manager = new $class();
        $manager->set(static::getOptionDefaults());
        $manager->define('adds', []);
        $manager->define('removes', []);

        return $manager;
    }

    /**
     * Return the default values for the manager of this provider.
     *
     * @return array
     */
    public static function getTypeDefaults(): array
    {
        return \array_filter(config('type.configurations'));
    }

    /**
     * Generate the corresponded manager.
     *
     * @return LaramoreManager
     */
    public static function generateTypeManager(): LaramoreManager
    {
        $class = config('type.manager');

        $manager = new $class();
        $manager->set(static::getTypeDefaults());
        $manager->define('default_options', ['visible', 'fillable', 'required']);

        return $manager;
    }

    /**
     * Return the default values for the manager of this provider.
     *
     * @return array
     */
    public static function getMetaDefaults(): array
    {
        $classes = config('meta.configurations');

        switch ($classes) {
            case 'automatic':
                $modelClasses = (new ReflectionNamespace(config('meta.models_namespace')))->getClassNames();
                $modelClasses = \array_filter($modelClasses, function ($class) {
                    return (new ReflectionClass($class))->implementsInterface(LaramoreModel::class);
                });

                $pivotClasses = (new ReflectionNamespace(config('meta.pivots_namespace')))->getClassNames();
                $pivotClasses = \array_filter($pivotClasses, function ($class) {
                    return (new ReflectionClass($class))->implementsInterface(LaramoreModel::class);
                });

                $classes = \array_merge($modelClasses, $pivotClasses);

                app('config')->set('meta.configurations', $classes);

                return $classes;

            case 'disabled':
                app('config')->set('meta.configurations', []);

                return [];

            default:
                if (\is_array($classes)) {
                    return $classes;
                }
        }

        throw new ConfigException(
            'meta.configurations',
            ["'automatic'", "'base'", "'disabled'", 'array of class names'],
            $classes
        );
    }

    /**
     * Generate the corresponded manager.
     *
     * @return LaramoreManager
     */
    public static function generateMetaManager(): LaramoreManager
    {
        $class = config('meta.manager');

        static::$metaManager = new $class(static::getMetaDefaults());
        static::$metaManager->setPreparing();

        return static::$metaManager;
    }

    /**
     * Return the default values for the manager of this provider.
     *
     * @return array
     */
    public static function getConstraintDefaults(): array
    {
        return config('field.constraint.configurations');
    }

    /**
     * Generate the corresponded manager.
     *
     * @return LaramoreManager
     */
    public static function generateConstraintManager(): LaramoreManager
    {
        $class = config('field.constraint.manager');

        return new $class(static::getConstraintDefaults());
    }

    /**
     * Return the default values for the manager of this provider.
     *
     * @return array
     */
    public static function getOperatorDefaults(): array
    {
        return \array_filter(config('operator.configurations'));
    }

    /**
     * Generate the corresponded manager.
     *
     * @return LaramoreManager
     */
    public static function generateOperatorManager(): LaramoreManager
    {
        $class = config('operator.manager');

        $manager = new $class();
        $manager->set(static::getOperatorDefaults());
        $manager->define('value_type', OperatorElement::MIXED_TYPE);
        $manager->define('fallback', '=');

        return $manager;
    }
    
    /**
     * Return the default values for the manager of this provider.
     *
     * @return array
     */
    public static function getProxyDefaults(): array
    {
        return config('proxy.configurations');
    }

    /**
     * Generate the corresponded manager.
     *
     * @return LaramoreManager
     */
    public static function generateProxyManager(): LaramoreManager
    {
        $class = config('proxy.manager');

        return new $class(static::getProxyDefaults());
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
        Type::lock();
        Meta::lock();
        Proxy::lock();
        FieldConstraint::lock();
    }
}
