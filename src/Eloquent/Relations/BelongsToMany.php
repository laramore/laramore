<?php

namespace Laramore\Eloquent\Relations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany as BaseBelongsToMany;


class BelongsToMany extends BaseBelongsToMany
{
    /**
     * Get the select columns for the relation query.
     *
     * @param  array  $columns
     * @return array
     */
    protected function shouldSelect(array $columns = ['*'])
    {
        foreach ($columns as $key => $column) {
            if ($column === '*') {
                unset($columns[$key]);

                $columns = array_unique([...array_map(function ($column) {
                    return $this->related->getTable().'.'.$column;
                }, $this->related->select), ...$columns]);
            }
        }

        return array_merge($columns, $this->aliasedPivotColumns());
    }

    /**
     * Get all of the IDs from the given mixed value.
     *
     * @param  mixed  $value
     * @return array
     */
    protected function parseIds($value)
    {
        $field = $this->related::getMeta()->getField($this->relatedKey);

        return array_map(function ($id) use ($field) {
            return $field->dry($id);
        }, parent::parseIds($value));
    }

    /**
     * Get the pivot attributes from a model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return array
     */
    protected function migratePivotAttributes(Model $model)
    {
        $values = [];

        foreach ($model->getExtras() as $key => $value) {
            // To get the pivots attributes we will just take any of the attributes which
            // begin with "pivot_" and add those to this arrays, as well as unsetting
            // them from the parent's models since they exist in a different table.
            if (strpos($key, 'pivot_') === 0) {
                $values[substr($key, 6)] = $value;

                unset($model->$key);
            }
        }

        return $values;
    }
}
