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

class HasMany extends LinkField
{
    protected $off;
    protected $from;
    protected $on;
    protected $to;

    public function getValue($model, $value)
    {
        return $this->relationValue($model)->first();
    }

    protected function owning()
    {
        parent::owning();

        if (is_null($this->off)) {
            try {
                throw new \Exception('You need to specify `off`');
            } catch (\Exception $e) {
                dd($this);
            }
        }

        $this->off::getMeta()->set($this->name, $this);
    }

    public function setValue($model, $value)
    {
        return $this->relationValue($model)->sync($value);
    }

    public function relationValue($model)
    {
        return $model->hasMany($this->on, $this->to);
    }

    public function whereValue($model, ...$args)
    {
        // return $model->where($this->name, ...$args);
    }
}
