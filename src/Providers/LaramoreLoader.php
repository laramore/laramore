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
use Laramore\Observers\ModelObservableManager;
use Laramore\{
    TypeManager, Meta, MetaManager
};

class LaramoreLoader extends ServiceProvider
{
    protected $modelObserverManager;
    protected $typeManager;
    protected $metaManager;

    protected $defaultTypes = [
        'boolean',
        'increment',
        'integer',
        'unsignedInteger',
        'char',
        'text',
        'string',
        'datetime',
        'timestamp',
    ];

    /**
     * Prepare all metas and lock them.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('ModelObservableManager', function() {
            return $this->modelObserverManager;
        });

        $this->app->singleton('TypeManager', function() {
            return $this->typeManager;
        });

        $this->app->singleton('MetaManager', function() {
            return $this->metaManager;
        });

        $this->modelObserverManager = new ModelObservableManager();
        $this->typeManager = new TypeManager($this->defaultTypes);
        $this->metaManager = new MetaManager('App\Models');

        $this->app->booted($this->bootedCallback());
    }

    /**
     * Lock all models after the booting is finished.
     *
     * @return void
     */
    protected function bootedCallback()
    {
        return function () {
            $this->metaManager->lock();
            $this->typeManager->lock();
            $this->modelObserverManager->lock();
        };
    }
}
