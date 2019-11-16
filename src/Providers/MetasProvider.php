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
use Laramore\Exceptions\ConfigException;
use Laramore\Traits\Provider\MergesConfig;
use ReflectionNamespace;

class MetasProvider extends ServiceProvider implements IsALaramoreProvider
{
    use MergesConfig;

    /**
     * Meta manager.
     *
     * @var array
     */
    protected static $managers;

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

        $this->app->singleton('Metas', function() {
            return static::getManager();
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

        static::getManager()->lock();
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
                $classes = (new ReflectionNamespace(config('metas.namespace')))->getClassNames();
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
            'metas.configurations', ["'automatic'", "'base'", "'disabled'", 'array of class names'], $classes
        );
    }

    /**
     * Generate the corresponded manager.
     *
     * @param  string $key
     * @return IsALaramoreManager
     */
    public static function generateManager(string $key): IsALaramoreManager
    {
        $class = config('metas.manager');

        static::$managers[$key] = $manager = new $class();

        foreach (static::getDefaults() as $modelClass) {
            $modelClass::getMeta();
        }

        return $manager;
    }

    /**
     * Return the generated manager for this provider.
     *
     * @return IsALaramoreManager
     */
    public static function getManager(): IsALaramoreManager
    {
        $appHash = \spl_object_hash(app());

        if (!isset(static::$managers[$appHash])) {
            return static::generateManager($appHash);
        }

        return static::$managers[$appHash];
    }
}
