<?php

namespace Laramore\Fields;

class HasMany extends LinkField
{
    public function getValue($model, $value)
    {
        return $this->relationValue($model)->first();
    }

    protected function owning()
    {
        parent::owning();

        if (is_null($this->on)) {
            throw new \Exception('You need to specify `on`');
        }

        $this->on::getMeta()->set($this->name, $this);
    }

    public function setValue($model, $value)
    {
        return $this->relationValue($model)->sync($value);
    }

    public function relationValue($model)
    {
        return $model->hasMany($this->to, $this->on, $this->from);
    }

    public function whereValue($model, ...$args)
    {
        // return $model->where($this->name, ...$args);
    }
}
