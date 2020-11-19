<?php
/**
 * Prepare type manager.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Container\Container;
use Laramore\Contracts\{
	Manager\LaramoreManager, Provider\LaramoreProvider
};
use Laramore\Exceptions\ConfigException;
use Laramore\Facades\GrammarType;
use ReflectionNamespace;

class GrammarTypeProvider extends ServiceProvider implements LaramoreProvider
{
    /**
     * Register our facade and create the manager.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/grammar.php', 'grammar',
        );

        $this->app->singleton('GrammarType', function() {
            return static::generateManager();
        });

        $this->app->booted([$this, 'bootedCallback']);
    }

    /**
     * Publish the config linked to the manager.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/grammar.php' => $this->app->make('path.config').'/grammar.php',
        ]);
    }

    /**
     * Return the default values for the manager of this provider.
     *
     * @return array
     */
    public static function getDefaults(): array
    {
        $config = Container::getInstance()->config;
        $classes = $config->get('grammar.configurations');

        switch ($classes) {
            case 'automatic':
                $classes = (new ReflectionNamespace($config->get('grammar.namespace')))->getClassNames();
                $config->set('grammar.configurations', $classes);

                return $classes;

            case 'disabled':
                return [];

            case 'base':
                return $config->get('grammar.namespace');

            default:
                if (\is_array($classes)) {
                    return $classes;
                }
        }

        throw new ConfigException(
            'grammar.configurations', ["'automatic'", "'base'", "'disabled'", 'array of class names'], $classes
        );
    }

    /**
     * Generate the corresponded manager.
     *
     * @return LaramoreManager
     */
    public static function generateManager(): LaramoreManager
    {
        $class = Container::getInstance()->config->get('grammar.manager');

        $manager = new $class();

        foreach (static::getDefaults() as $class) {
            if ($manager->doesManage($class)) {
                $manager->createHandler($class);
            }
        }

        return $manager;
    }

    /**
     * Lock all managers after booting.
     *
     * @return void
     */
    public function bootedCallback()
    {
        GrammarType::lock();
    }
}
