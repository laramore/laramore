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
use Laramore\Contracts\Field\RelationField;

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

    /**
     * Dynamically handle calls to the class.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return parent::__call($method, $parameters);
        }

        $model = $this->first();

        if (! $model) {
            throw new \LogicException('Unable to create query for empty collection.');
        }

        $class = get_class($model);
        $meta = $class::getMeta();

        if ($meta->hasField($method)) {
            $field = $meta->getField($method);

            if ($field instanceof RelationField) {

            } else {
                return array_map(function ($model) use ($field) {
                    return $field->get($model);
                }, $this->items);
            }
        }

        return parent::__call($method, $parameters);
    }
}
