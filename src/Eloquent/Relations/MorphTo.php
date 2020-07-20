<?php
/**
 * Fix little morph issues.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Eloquent\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphTo as BaseMorphTo;

class MorphTo extends BaseMorphTo
{
    /**
     * Build a dictionary with the models.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    protected function buildDictionary(Collection $models)
    {
        foreach ($models as $model) {
            if ($model->{$this->morphType}) {
                $this->dictionary[$model::getActualClassNameForMorph($model->{$this->morphType})][$model->{$this->foreignKey}][] = $model;
            }
        }
    }
}
