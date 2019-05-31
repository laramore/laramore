<?php
/**
 * Define a reverse manytomany field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields\Link;

class BelongsToMany extends LinkField
{
    protected $on;
    protected $to;
    protected $off;
    protected $from;
    protected $pivotMeta;
    protected $pivotTo;
    protected $pivotFrom;

    public function getValue($model, $value)
    {
        return $this->relationValue($model)->get();
    }

    protected function owning()
    {
        parent::owning();

        if (is_null($this->off)) {
            throw new \Exception('You need to specify `off`');
        }

        $this->defineProperty('pivotMeta', $this->getOwner()->pivotMeta);
        $this->defineProperty('pivotTo', $this->getOwner()->pivotTo);
        $this->defineProperty('pivotFrom', $this->getOwner()->pivotFrom);

        $this->off::getMeta()->set($this->name, $this);
    }

    public function setValue($model, $value)
    {
        return $this->relationValue($model)->sync($value);
    }

    public function relationValue($model)
    {
        return $model->be($this->on, $this->to);
    }

    public function whereValue($model, ...$args)
    {
        return $model->where($this->name, ...$args);
    }
}
