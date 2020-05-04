<?php
/**
 * Define the model collection.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Eloquent;

use Illuminate\Database\Eloquent\Collection;

class ModelCollection extends Collection
{
    /**
     * Set all models as fetching.
     *
     * @param boolean $fetching
     * @return self
     */
    public function fetching(bool $fetching=true)
    {
        return $this->each(function ($model) use ($fetching) {
            $model->fetching = $fetching;
        });
    }
}