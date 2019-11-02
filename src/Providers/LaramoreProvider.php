<?php
/**
 * Load and prepare Models.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Providers;

use Illuminate\Support\ServiceProvider;
use Laramore\Interfaces\IsALaramoreModel;
use Laramore\Observers\BaseManager;
use Laramore\Grammars\GrammarTypeManager;
use Laramore\Eloquent\ModelEventManager;
use Laramore\Validations\ValidationManager;
use Laramore\Proxies\ProxyManager;
use Laramore\Elements\{
    TypeManager, OperatorManager
};
use Laramore\{
    Meta, MetaManager
};
use ReflectionNamespace;

class LaramoreProvider extends ServiceProvider
{
    /**
     * Grammar and Model observer managers.
     *
     * @var BaseManager
     */
    protected $modelEvents;
    protected $proxies;

    /**
     * Meta manager.
     *
     * @var MetaManager
     */
    protected $metas;

    /**
     * Default model namespace.
     *
     * @var string
     */
    protected $modelNamespace = 'App\\Models';

    /**
     * Prepare all singletons and add booting and booted \Closures.
     *
     * @return void
     */
    public function register()
    {
        $this->createSigletons();
        $this->createObjects();

        $this->mergeConfigFrom(
            __DIR__.'/../../config/models.php', 'models',
        );

        $this->app->booting([$this, 'bootingCallback']);
        $this->app->booted([$this, 'bootedCallback']);
    }

    /**
     * Create all singletons: GrammarTypeManager, ModelEventManager, ProxyManager, TypeManager, MetaManager.
     *
     * @return void
     */
    protected function createSigletons()
    {
        $this->app->singleton('ModelEvents', function() {
            return $this->modelEvents;
        });

        $this->app->singleton('Proxies', function() {
            return $this->proxies;
        });

        $this->app->singleton('Validations', function() {
            return $this->validations;
        });
    }

    /**
     * Create all singleton objects: GrammarTypeManager, ModelEventManager, ProxyManager, TypeManager, MetaManager.
     *
     * @return void
     */
    protected function createObjects()
    {
        $this->modelEvents = new ModelEventManager;
        $this->proxies = new ProxyManager;
        $this->validations = new ValidationManager;
        $this->metas = new MetaManager;
    }

    /**
     * Add all metas to the MetaManager from a specific namespace.
     *
     * @return void
     */
    protected function createMetas()
    {
        $this->app->singleton('Metas', function() {
            return $this->metas;
        });

        foreach ((new ReflectionNamespace($this->modelNamespace))->getClasses() as $modelClass) {
            if ($modelClass->implementsInterface(IsALaramoreModel::class)) {
                $modelClass->getName()::getMeta();
            }
        }
    }

    /**
     * Prepare metas and grammar observable handlers before booting.
     *
     * @return void
     */
    public function bootingCallback()
    {
        $this->createMetas();
    }

    /**
     * Publish config file.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/models.php' => config_path('models.php'),
        ]);
    }

    /**
     * Lock all managers after booting.
     *
     * @return void
     */
    public function bootedCallback()
    {
        $this->metas->lock();
        $this->modelEvents->lock();
        $this->proxies->lock();
    }
}
