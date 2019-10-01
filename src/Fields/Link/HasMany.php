<?php
/**
 * Define a reverse foreign field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields\Link;

use Illuminate\Support\Collection;
use Laramore\Elements\Operator;
use Laramore\Eloquent\Builder;
use Laramore\Interfaces\{
    IsProxied, IsALaramoreModel
};
use Op;

class HasMany extends LinkField
{
    protected $off;
    protected $from;
    protected $on;
    protected $to;

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
        if ($value instanceof Collection) {
            return $value;
        }

        if (\is_null($value) || \is_array($value)) {
            return collect($value);
        }

        if (!($value instanceof $this->on)) {
            return collect($value);
        }

        $model = new $this->on;
        $model->setRawAttribute($model->getKeyName(), $value);

        return collect($model);
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
        $attname = $this->on::getMeta()->getPrimary()->attname;

        return $this->whereNull($builder, $value, $boolean, $not, function ($query) use ($attname, $value) {
            return $query->whereIn($attname, $value);
        });
    }

    public function whereNotIn(Builder $builder, Collection $value=null, $boolean='and')
    {
        return $this->whereIn($builder, $value, $boolean, true);
    }

    public function where(Builder $builder, Operator $operator, $value=null, $boolean='and', int $count=null)
    {
        $attname = $this->on::getMeta()->getPrimary()->attname;

        return $this->whereNotNull($builder, $value, $boolean, $operator, ($count ?? count($value)), function ($query) use ($attname, $value) {
            return $query->whereIn($attname, $value);
        });
    }

    public function retrieve(IsALaramoreModel $model)
    {
        return $this->getOwner()->relateFieldAttribute($this, $model)->getResults();
    }

    public function consume(IsALaramoreModel $model, $value)
    {
        $field = $this->getField('id');
        $field->getOwner()->setFieldAttribute($field, $model, $value[$this->to]);

        return $value;
    }

    public function relate($model)
    {
        return $model->hasMany($this->on, $this->to, $this->from);
    }
}
