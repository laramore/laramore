<?php
/**
 * Define a reverse one to one field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

use Illuminate\Support\Collection;
use Laramore\Elements\Operator;
use Laramore\Eloquent\Builder;
use Laramore\Fields\CompositeField;
use Laramore\Interfaces\{
    IsProxied, IsALaramoreModel
};
use Op;

class HasOne extends LinkField
{
    protected $off;
    protected $from;
    protected $on;
    protected $to;

    public function getReversed(): CompositeField
    {
        return $this->getOwner();
    }

    public function cast($value)
    {
        return $this->transform($value);
    }

    public function dry($value)
    {
        return $this->transform($value)->map(function ($value) {
            return $value[$this->from];
        });
    }

    public function transform($value)
    {
        if (\is_null($value) || ($value instanceof $this->on)) {
            return $value;
        }

        $model = new $this->on;
        $model->setRawAttribute($model->getKeyName(), $value);

        return $model;
    }

    public function serialize($value)
    {
        return $value;
    }

    public function whereNull(Builder $builder, $value=null, $boolean='and', $not=false, \Closure $callback=null)
    {
        if ($not) {
            return $this->whereNotNull($builder, $value, $boolean, null, null, $callback);
        }

        return $builder->doesntHave($this->name, $boolean, $callback);
    }

    public function whereNotNull(Builder $builder, $value=null, $boolean='and', $operator=null, int $count=null, \Closure $callback=null)
    {
        return $builder->has($this->name, (string) ($operator ?? Op::supOrEq()), ($count ?? 1), $boolean, $callback);
    }

    public function whereIn(Builder $builder, Collection $value=null, $boolean='and', $not=false)
    {
        $builder->getQuery()->whereIn($this->on::getMeta()->getPrimary()->attname, $value, $boolean, $not);

        return $builder;
    }

    public function whereNotIn(Builder $builder, Collection $value=null, $boolean='and')
    {
        return $this->whereIn($builder, $value, $boolean, true);
    }

    public function where(Builder $builder, Operator $operator, $value=null, $boolean='and')
    {
        $builder->getQuery()->where($this->on::getMeta()->getPrimary()->attname, (string) $operator, $value, $boolean);

        return $builder;
    }

    public function retrieve(IsALaramoreModel $model)
    {
        return $this->relate($model)->getResults();
    }

    public function consume(IsALaramoreModel $model, $value)
    {
        $value = $this->transform($value);
        $value->setAttribute($this->getReversed()->name, $model);

        return $value;
    }

    public function relate(IsProxied $model)
    {
        return $model->hasOne($this->on, $this->to, $this->from);
    }

    public function reverbate(IsALaramoreModel $model, $value): bool
    {
        $attname = $this->on::getMeta()->getPrimary()->attname;
        $id = $model->getKey();
        $valueId = $value[$attname];

        $this->on::where($this->to, $id)->where($attname, $valueId)->update([$this->to => null]);
        $this->on::where($attname, $valueId)->update([$this->to => $id]);

        return true;
    }
}
