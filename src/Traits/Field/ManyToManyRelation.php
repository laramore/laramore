<?php
/**
 * Add multiple methods for many to many relations.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Traits\Field;

use Illuminate\Support\{
    Collection, Str
};
use Laramore\Elements\Operator;
use Laramore\Eloquent\Builder;
use Laramore\Fields\Field;
use Laramore\Interfaces\{
    IsALaramoreModel, IsProxied
};
use Op;

trait ManyToManyRelation
{
    protected $on;
    protected $to;
    protected $off;
    protected $from;
    protected $pivotMeta;
    protected $pivotTo;
    protected $pivotFrom;

    protected function setProxies()
    {
        parent::setProxies();

        $this->setProxy('attach', ['model']);
        $this->setProxy('detach', ['model']);
        $this->setProxy('sync', ['model']);
        $this->setProxy('toggle', ['model']);
        $this->setProxy('syncWithoutDetaching', ['model']);
        $this->setProxy('updateExistingPivot', ['model']);
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

    public function transformToModel($value)
    {
        if ($value instanceof $this->on) {
            return $value;
        }

        $model = new $this->on;
        $model->setRawAttribute($model->getKeyName(), $value);

        return $model;
    }

    public function transform($value)
    {
        if ($value instanceof Collection) {
            return $value;
        }

        if (\is_null($value) || \is_array($value)) {
            return collect($value);
        }

        return collect($this->transformToModel($value));
    }

    public function serialize($value)
    {
        return $value;
    }

    public function retrieve(IsALaramoreModel $model)
    {
        return $this->relate($model)->getResults();
    }

    public function relate(IsProxied $model)
    {
        return $model->belongsToMany($this->on, $this->pivotMeta->getTableName(), $this->pivotTo->from, $this->pivotFrom->from, $this->to, $this->from, $this->name)
            ->withPivot(...\array_map(function (Field $field) {
                return $field->attname;
            }, \array_values($this->pivotMeta->getFields())));
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

    public function consume(IsALaramoreModel $model, $value)
    {
        $relationName = $this->getReversed()->name;
        $collections = collect();

        foreach ($value as $element) {
            if ($element instanceof $this->on) {
                $collections->add($element);
            } else {
                $collections->add($element = $this->transformToModel($element));
            }

            $element->setAttribute($relationName, $model);
        }

        return $collections;
    }

    public function reverbate(IsALaramoreModel $model, $value): bool
    {
        $this->sync($model, $value);

        return true;
    }

    public function attach(IsALaramoreModel $model, $value)
    {
        \call_user_func([$model, $this->name])->attach($value);

        return $model;
    }

    public function detach(IsALaramoreModel $model, $value)
    {
        \call_user_func([$model, $this->name])->detach($value);

        return $model;
    }

    public function sync(IsALaramoreModel $model, $value)
    {
        \call_user_func([$model, $this->name])->sync($value);

        return $model;
    }

    public function toggle(IsALaramoreModel $model, $value)
    {
        \call_user_func([$model, $this->name])->toggle($value);

        return $model;
    }

    public function syncWithoutDetaching(IsALaramoreModel $model, $value)
    {
        \call_user_func([$model, $this->name])->syncWithoutDetaching($value);

        return $model;
    }

    public function updateExistingPivot(IsALaramoreModel $model, $value)
    {
        \call_user_func([$model, $this->name])->updateExistingPivot($value);

        return $model;
    }
}
