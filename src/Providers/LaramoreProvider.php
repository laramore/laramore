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
    protected $grammarTypes;
    protected $modelEvents;
    protected $proxies;

    /**
     * Type manager.
     *
     * @var TypeManager
     */
    protected $types;

    /**
     * Meta manager.
     *
     * @var MetaManager
     */
    protected $metas;

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
        'boolean' => 'bool',
        'integer' => 'integer',
        'unsignedInteger' => 'integer',
        'increment' => 'integer',
        'string' => 'string',
        'text' => 'string',
        'char' => 'string',
        'timestamp' => 'string',
        'datetime' => 'string',
        'enum' => 'enum',
    ];

    /**
     * Default types to create.
     *
     * @var array
     */
    protected $defaultOperators = [
        'null' => ['null', 'null'],
        'isNull' => ['null', 'null'],
        'notNull' => ['notNull', 'null'],
        'isNotNull' => ['notNull', 'null'],
        'doesntExist' => ['null', 'null'],
        'dontExist' => ['null', 'null'],
        'exist' => ['notNull', 'null'],
        'exists' => ['notNull', 'null'],
        'equal' => '=',
        'inf' => '<',
        'sup' => '>',
        'infOrEq' => '<=',
        'supOrEq' => '>=',
        'safeNotEqual' => '<>',
        'notEqual' => '!=',
        'safeEqual' => '<=>',
        'like' => 'like',
        'likeBinary' => 'like binary',
        'notLike' => 'not like',
        'ilike' => 'ilike',
        'notIlike' => 'not ilike',
        'rlike', 'rlike',
        'regexp' => 'regexp',
        'notRegexp' => 'not regexp',
        'similarTo' => 'similar to',
        'notSimilarTo' => 'not similar to',
        'bitand' => ['&', 'binary'],
        'bitor' => ['|', 'binary'],
        'bitxor' => ['^', 'binary'],
        'bitleft' => ['<<', 'binary'],
        'bitright' => ['>>', 'binary'],
        'match' => '~',
        'imatch' => '~*',
        'notMatch' => '!~',
        'notImatch' => '!~*',
        'same' => '~~',
        'isame' => '~~*',
        'notSame' => '!~~',
        'notIsame' => '!~~*',
        'in' => ['in', 'collection'],
        'notIn' => ['not in', 'collection'],
    ];

    /**
     * Prepare all singletons and add booting and booted \Closures.
     *
     * @return void
     */
    public function register()
    {
        $this->createSigletons();
        $this->createObjects();

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
        $this->app->singleton('GrammarTypes', function() {
            return $this->grammarTypes;
        });

        $this->app->singleton('ModelEvents', function() {
            return $this->modelEvents;
        });

        $this->app->singleton('Proxies', function() {
            return $this->proxies;
        });

        $this->app->singleton('Types', function() {
            return $this->types;
        });

        $this->app->singleton('Operators', function() {
            return $this->operators;
        });

        $this->app->singleton('Validations', function() {
            return $this->validations;
        });

        $this->app->singleton('Metas', function() {
            return $this->metas;
        });
    }

    /**
     * Create all singleton objects: GrammarTypeManager, ModelEventManager, ProxyManager, TypeManager, MetaManager.
     *
     * @return void
     */
    protected function createObjects()
    {
        $this->grammarTypes = new GrammarTypeManager;
        $this->modelEvents = new ModelEventManager;
        $this->proxies = new ProxyManager;
        $this->types = new TypeManager($this->defaultTypes);
        $this->operators = new OperatorManager($this->defaultOperators);
        $this->validations = new ValidationManager;
        $this->metas = new MetaManager;
    }

    /**
     * Add all metas to the MetaManager from a specific namespace.
     *
     * @return void
     */
    protected function addMetas()
    {
        foreach ((new ReflectionNamespace($this->modelNamespace))->getClasses() as $modelClass) {
            if ($modelClass->implementsInterface(IsALaramoreModel::class)) {
                $modelClass->getName()::getMeta();
            }
        }
    }

    /**
     * Create grammar observable handlers for each possible grammars and add them to the GrammarTypeManager.
     *
     * @return void
     */
    protected function createGrammarObservers()
    {
        foreach ((new ReflectionNamespace($this->grammarNamespace))->getClassNames() as $class) {
            if ($this->grammarTypes->doesManage($class)) {
                $this->grammarTypes->createHandler($class);
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
        $this->addMetas();
        $this->createGrammarObservers();
    }

    /**
     * Lock all managers after booting.
     *
     * @return void
     */
    public function bootedCallback()
    {
        $this->metas->lock();
        $this->types->lock();
        $this->operators->lock();
        $this->modelEvents->lock();
        $this->proxies->lock();
        $this->grammarTypes->lock();
    }
}
