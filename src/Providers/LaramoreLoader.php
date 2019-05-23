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
use Laramore\{
    TypeManager, Meta, MetaManager
};

class LaramoreLoader extends ServiceProvider
{
    protected $typeManager;
    protected $metaManager;

    protected $defaultTypes = [
        'boolean',
        'increment',
        'integer',
        'unsignedInteger',
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
        $this->app->singleton('TypeManager', function() {
            return $this->typeManager;
        });

        $this->app->singleton('MetaManager', function() {
            return $this->metaManager;
        });

        $this->metaManager = new MetaManager('App\Models');
        $this->typeManager = new TypeManager($this->defaultTypes);

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
        };
    }
}
