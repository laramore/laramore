<?php
/**
 * Define a foreign field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

use Illuminate\Support\{
    Str, Collection
};
use Laramore\Elements\Operator;
use Laramore\Eloquent\Builder;
use Laramore\Fields\LinkField;
use Laramore\Traits\Field\OneToOneRelation;
use Laramore\Interfaces\{
    IsALaramoreModel, IsProxied
};

class Foreign extends CompositeField
{
    use OneToOneRelation;

    protected static $defaultLinks = [
        'reversed' => HasMany::class,
    ];

    public function consume(IsALaramoreModel $model, $value)
    {
        $model->setAttribute($this->getField('id')->attname, $value[$this->to]);

        return $value;
    }

    /**
     * Return the query with this field as condition.
     *
     * @param  Builder $query
     * @param  mixed   ...$args
     * @return Builder
     */
    public function relate(IsProxied $model)
    {
        return $model->belongsTo($this->on, $this->from, $this->to);
    }

    public function reverbate(IsALaramoreModel $model, $value): bool
    {
        return $value->save();
    }
}
