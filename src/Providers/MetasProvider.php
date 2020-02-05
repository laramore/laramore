<?php
/**
 * Generate the Metas manager.
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
use Laramore\Traits\Provider\MergesConfig;
use ReflectionNamespace;

class MetasProvider extends ServiceProvider implements IsALaramoreProvider
{
    use MergesConfig;

    protected static $manager;

    /**
     * Register our facade and create the manager.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/metas.php', 'metas',
        );

        $this->mergeConfigFrom(
            __DIR__.'/../../config/field/proxies.php', 'field.proxies',
        );

        $this->app->singleton('Metas', function() {
            if (\is_null(static::$manager)) {
                return static::generateManager();
            }

            return static::$manager;
        });
    }

    /**
     * Publish the config linked to the manager.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/metas.php' => config_path('metas.php'),
        ]);

        $this->app->register(LastProvider::class);
    }

    /**
     * Return the default values for the manager of this provider.
     *
     * @return array
     */
    public static function getDefaults(): array
    {
        $classes = config('metas.configurations');

        switch ($classes) {
            case 'automatic':
                $classes = (new ReflectionNamespace(config('metas.models_namespace')))->getClassNames();
                $classes = \array_filter($classes, function ($class) {
                    return (new \ReflectionClass($class))->implementsInterface(IsALaramoreModel::class);
                });

                app('config')->set('metas.configurations', $classes);

                return $classes;

            case 'disabled':
                return [];

            case 'base':
                return config('metas.namespace');

            default:
                if (\is_array($classes)) {
                    return $classes;
                }
        }

        throw new ConfigException(
            'metas.configurations',
            ["'automatic'", "'base'", "'disabled'", 'array of class names'],
            $classes
        );
    }

    /**
     * Generate the corresponded manager.
     *
     * @return IsALaramoreManager
     */
    public static function generateManager(): IsALaramoreManager
    {
        $class = config('metas.manager');

        static::$manager = new $class();

        foreach (static::getDefaults() as $modelClass) {
            if (!static::$manager->has($modelClass)) {
                $modelClass::generateMeta();
            }
        }

        return static::$manager;
    }
}
