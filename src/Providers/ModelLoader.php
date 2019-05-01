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
use ReflectionNamespace;
use Laramore\Traits\Model\HasLaramore;

class ModelLoader extends ServiceProvider
{
    protected static $metas = [];

    /**
     * Prepare all metas and lock them.
     *
     * @return void
     */
    public function register()
    {
        $modelNamespace = new ReflectionNamespace('App\Models');
        $metas = [];

        foreach ($modelNamespace->getClasses() as $modelClass) {
            if (in_array(HasLaramore::class, $modelClass->getTraitNames())) {
                static::$metas[] = $modelClass->getName()::prepareMeta();
            }
        }

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
            foreach (static::$metas as $meta) {
                $meta->lock();
            }

            foreach (static::$metas as $meta) {
                if (!$meta->isLocked()) {
                    throw new \Exception('All metas are not locked properly');
                }

                foreach ($meta->allFields() as $field) {
                    if (!$field->isLocked()) {
                        throw new \Exception('All fields are not locked by their owner');
                    }
                }
            }
        };
    }
}
