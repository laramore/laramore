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
use Laramore\Traits\Model\HasLaramore;
use Laramore\Observers\{
    GrammarObservableManager, ModelObservableManager
};
use Laramore\{
    TypeManager, Meta, MetaManager
};
use ReflectionNamespace;

class LaramoreLoader extends ServiceProvider
{
    /**
     * Grammar and Model observer managers.
     *
     * @var BaseObservableManager
     */
    protected $grammarObservableManager;
    protected $modelObserverManager;

    /**
     * Type manager.
     *
     * @var TypeManager
     */
    protected $typeManager;

    /**
     * Meta manager.
     *
     * @var MetaManager
     */
    protected $metaManager;

    /**
     * Default grammar namespace.
     *
     * @var string
     */
    protected $grammarNamespace = 'Illuminate\\Database\\Schema\\Grammars';

    /**
     * Default model namespace.
     *
     * @var string
     */
    protected $modelNamespace = 'App\\Models';

    /**
     * Default types to create.
     *
     * @var array
     */
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
     * Prepare all singletons and add booting and booted callbacks.
     *
     * @return void
     */
    public function register()
    {
        $this->createSigletons();
        $this->createObjects();

        $this->app->booting($this->bootingCallback);
        $this->app->booted($this->bootedCallback);
    }

    /**
     * Create all singletons: GrammarObservableManager, ModelObservableManager, TypeManager, MetaManager.
     *
     * @return void
     */
    protected function createSigletons()
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
    }

    /**
     * Create all singleton objects: GrammarObservableManager, ModelObservableManager, TypeManager, MetaManager.
     *
     * @return void
     */
    protected function createObjects()
    {
        $this->grammarObservableManager = new GrammarObservableManager;
        $this->modelObserverManager = new ModelObservableManager;
        $this->typeManager = new TypeManager($this->defaultTypes);
        $this->metaManager = new MetaManager;
    }

    /**
     * Add all metas to the MetaManager from a specific namespace.
     *
     * @return void
     */
    protected function addMetas()
    {
        foreach ((new ReflectionNamespace($this->modelNamespace))->getClasses() as $modelClass) {
            if (\in_array(HasLaramore::class, $modelClass->getTraitNames())) {
                $this->metaManager->addMeta($modelClass->getName()::getMeta());
            }
        }
    }

    /**
     * Create grammar observable handlers for each possible grammars and add them to the GrammarObservableManager.
     *
     * @return void
     */
    protected function createGrammarObservers()
    {
        foreach ((new ReflectionNamespace($this->grammarNamespace))->getClassNames() as $class) {
            if ($this->grammarObservableManager->isObservable($class)) {
                $this->grammarObservableManager->createObservableHandler($class);
            }
        }
    }

    /**
     * Prepare metas and grammar observable handlers before booting.
     *
     * @return void
     */
    protected function bootingCallback()
    {
        $this->addMetas();
        $this->createGrammarObservers();
    }

    /**
     * Lock all managers after booting.
     *
     * @return void
     */
    protected function bootedCallback()
    {
        $this->metaManager->lock();
        $this->typeManager->lock();
        $this->modelObserverManager->lock();
        $this->grammarObservableManager->lock();
    }
}
