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
use Laramore\Contracts\Eloquent\LaramoreCollection;

class ModelCollection extends Collection implements LaramoreCollection
{
    /**
     * Set all models as fetchingDatabase.
     *
     * @param boolean $fetchingDatabase
     * @return self
     */
    public function fetchingDatabase(bool $fetchingDatabase=true)
    {
        return $this->each(function ($model) use ($fetchingDatabase) {
            $model->fetchingDatabase = $fetchingDatabase;
        });
    }

    public function query()
    {
        return $this->toQuery();
    }
}
