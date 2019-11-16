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
     * Prepare all singletons and add booting and booted \Closures.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(MetasProvider::class);

        $this->createSigletons();
        $this->createObjects();

        $this->app->booted([$this, 'bootedCallback']);
    }

    protected function getDefaults()
    {
        return [];
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
    }

    /**
     * Lock all managers after booting.
     *
     * @return void
     */
    public function bootedCallback()
    {
        $this->modelEvents->lock();
        $this->proxies->lock();
        $this->validations->lock();
    }
}
