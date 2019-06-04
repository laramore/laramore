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

trait ManyToManyRelation
{
    protected $on;
    protected $to;
    protected $off;
    protected $from;
    protected $pivotMeta;
    protected $pivotTo;
    protected $pivotFrom;

    public function castValue($model, $value)
    {
        if (is_null($value) || $value instanceof $this->on) {
            return $value;
        } else {
            $model = new $this->on;
            $model->setAttribute($this->to, $value, true);

            return $model;
        }
    }

    public function getValue($model, $value)
    {
        return $this->relationValue($model)->get();
    }

    public function setValue($model, $value)
    {
        return $this->sync($model, $value);
    }

    public function relationValue($model)
    {
        return $model->belongsToMany($this->on, $this->pivotMeta->getTableName(), $this->pivotTo, $this->pivotFrom);
    }

    public function whereValue($query, ...$args)
    {
        if (count($args) > 1) {
            [$operator, $value] = $args;
        } else {
            $operator = '=';
            $value = $args[0] ?? null;
        }

        if (is_object($value)) {
            $value = $value->{$this->on};
        } else if (!is_null($value)) {
            $value = (integer) $value;
        }

        return $query->where($this->from, $operator, $value);
    }

    public function setFieldValue($model, $field, $value)
    {
        return $field->setValue($model, $value);
    }

    public function getFieldValue($model, $field, $value)
    {
        return $field->getValue($model, $value);
    }

    public function attachValue($model, ...$args)
    {
        return ($this->relationValue()->attach(...$args) ?? $model);
    }

    public function detachValue($model, ...$args)
    {
        return ($this->relationValue()->detach(...$args) ?? $model);
    }

    public function syncValue($model, ...$args)
    {
        return ($this->relationValue()->sync(...$args) ?? $model);
    }

    public function toggleValue($model, ...$args)
    {
        return ($this->relationValue()->toggle(...$args) ?? $model);
    }

    public function syncWithoutDetachingValue($model, ...$args)
    {
        return ($this->relationValue()->syncWithoutDetaching(...$args) ?? $model);
    }

    public function updateExistingPivotValue($model, ...$args)
    {
        return ($this->relationValue()->updateExistingPivotValue(...$args) ?? $model);
    }
}
