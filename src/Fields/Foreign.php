<?php

namespace Laramore\Fields;

use Illuminate\Support\Str;

class Foreign extends CompositeField
{
    protected static $defaultFields = [
        'id' => Number::class,
    ];
    protected static $defaultLinks = [
        'reversed' => HasMany::class,
    ];

    public function on(string $model, string $reversedName=null)
    {
        $this->checkLock();

        $this->properties['on'] = $this->fields['id']->on = $this->links['reversed']->off = $model;

        $this->reversedName($reversedName);

        return $this;
    }

    public function reversedName(string $reversedName=null)
    {
        $this->linksName['reversed'] = $reversedName ?: '*{modelname}';

        return $this;
    }

    public function from(string $column)
    {
        $this->checkLock();

        return $this;
    }

    public function owning()
    {
        $this->links['reversed']->on = $this->getOwner()->getModelClass();

        parent::owning();

        $this->properties['reversed'] = $this->links['reversed']->name;
        $this->properties['from'] = $this->fields['id']->from = $this->links['reversed']->to = $this->fields['id']->attname;

        return $this;
    }

    protected function locking()
    {
        parent::locking();

        if (!$this->on) {
            throw new \Exception('Related model settings needed. Set it by calling `on` method');
        }
    }

    public function getValue($model, $value)
    {
        return $this->relationValue($model)->first();
    }

    public function setValue($model, $value)
    {
        $model->setAttribute($this->fields[0]->getName(), $value->{$this->on});
        $model->setRelation($this->name, $value);
    }
    //
    // public function relationValue($model)
    // {
    //     return $model->belongsTo($this->from, $this->fields[0]->getName(), $this->on);
    // }

    public function whereValue($query, ...$args)
    {
        if (count($args) > 1) {
            list($operator, $value) = $args;
        } else {
            $operator = '=';
            $value = $args[0] ?? null;
        }

        if (is_object($value)) {
            $value = $value->{$this->on};
        } else if (!is_null($value)) {
            $value = (integer) $value;
        }

        return $query->where($this->fields[0]->getName(), $operator, $value);
    }
}
