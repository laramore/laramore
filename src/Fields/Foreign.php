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

        $this->properties['on'] = $this->fields['id']->on = $this->links['reversed']->on = $model;

        $this->reversedName($reversedName);

        return $this;
    }

    public function reversedName(string $reversedName=null)
    {
        $this->linksName['reversed'] = $reversedName ?: '*{modelname}';

        return $this;
    }

    public function to(string $column)
    {
        $this->checkLock();

        $this->properties['on'] = $this->links['reversed']->from = $this->fields['id']->to = $column;

        return $this;
    }

    public function owning()
    {
        parent::owning();

        $this->links['reversed']->to = $this->getOwner()->getModelClass();
        $this->properties['reversed'] = $this->links['reversed']->name;

        return $this;
    }

    protected function locking()
    {
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

    public function relationValue($model)
    {
        return $model->belongsTo($this->to, $this->fields[0]->getName(), $this->on);
    }

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
