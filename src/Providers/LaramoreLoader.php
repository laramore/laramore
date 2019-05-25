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
use Laramore\Observers\{
    GrammarObservableManager, ModelObservableManager
};
use Laramore\{
    TypeManager, Meta, MetaManager
};
use ReflectionNamespace;

class LaramoreLoader extends ServiceProvider
{
    protected $grammarObservableManager;
    protected $modelObserverManager;
    protected $typeManager;
    protected $metaManager;
    protected $grammarNamespace = 'Illuminate\\Database\\Schema\\Grammars';
    protected $modelNamespace = 'App\\Models';

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
        $this->app->singleton('GrammarObservableManager', function() {
            return $this->grammarObservableManager;
        });

        $this->app->singleton('ModelObservableManager', function() {
            return $this->modelObserverManager;
        });

        $this->app->singleton('TypeManager', function() {
            return $this->typeManager;
        });

        $this->app->singleton('MetaManager', function() {
            return $this->metaManager;
        });

        $this->grammarObservableManager = new GrammarObservableManager;

        foreach ((new ReflectionNamespace($this->grammarNamespace))->getClassNames() as $class) {
            $this->grammarObservableManager->createObservableHandler($class);
        }

        $this->modelObserverManager = new ModelObservableManager();
        $this->typeManager = new TypeManager($this->defaultTypes);
        $this->metaManager = new MetaManager($this->modelNamespace);

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
            $this->grammarObservableManager->lock();
        };
    }
}
