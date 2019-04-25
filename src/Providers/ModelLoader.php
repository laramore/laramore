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
    /**
     * Prepare all metas and lock them.
     *
     * @return void
     */
    public function boot()
    {
        $modelNamespace = new ReflectionNamespace('App\Models');
        $metas = [];

        foreach ($modelNamespace->getClasses() as $modelClass) {
            if (in_array(HasLaramore::class, $modelClass->getTraitNames())) {
                $metas[] = $modelClass->getName()::prepareMeta();
            }
        }

        foreach ($metas as $meta) {
            $meta->lock();
        }

        foreach ($metas as $meta) {
            if (!$meta->isLocked()) {
                throw new \Exception('All metas are not locked properly');
            }

            foreach ($meta->allFields() as $field) {
                if (!$field->isLocked()) {
                    throw new \Exception('All fields are not locked by their owner');
                }
            }
        }
    }
}
