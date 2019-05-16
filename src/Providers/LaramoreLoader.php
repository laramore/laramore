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
use Laramore\{
    Meta, MetaManager
};

class LaramoreLoader extends ServiceProvider
{
    protected $metaManager;

    /**
     * Prepare all metas and lock them.
     *
     * @return void
     */
    public function register()
    {
        $this->metaManager = new MetaManager('App\Models');

        $this->app->booted($this->bootedCallback());
    }

    public function boot()
    {
        $this->app->singleton('MetaManager', function() {
            return $this->metaManager;
        });
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
        };
    }
}
