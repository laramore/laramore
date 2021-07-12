<?php

namespace Laramore\Eloquent\Relations;

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
}
