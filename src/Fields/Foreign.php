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

        $this->properties['on'] = $this->getField('id')->on = $this->getLink('reversed')->off = $model;
        $this->to($this->getLink('reversed')->off::getMeta()->getPrimary()->attname);

        $this->reversedName($reversedName);

        return $this;
    }

    public function to(string $name)
    {
        $this->checkLock();

        $this->properties['to'] = $this->getField('id')->to = $this->getLink('reversed')->from = $name;

        return $this;
    }

    public function reversedName(string $reversedName=null)
    {
        $this->linksName['reversed'] = $reversedName ?: '*{modelname}';

        return $this;
    }

    public function owning()
    {
        $this->getLink('reversed')->on = $this->getOwner()->getModelClass();

        parent::owning();

        return $this;
    }

    protected function locking()
    {
        if (!$this->on) {
            throw new \Exception('Related model settings needed. Set it by calling `on` method');
        }

        $this->properties['reversed'] = $this->getLink('reversed')->name;
        $this->properties['from'] = $this->getField('id')->from = $this->getLink('reversed')->to = $this->getField('id')->attname;

        parent::locking();
    }

    public function castValue($value)
    {
        if ($value instanceof $this->on) {
            return $value;
        } else {
            $model = new $this->on;
            $model->setAttribute($this->to, $value, true);

            return $model;
        }
    }

    public function getValue($model, $value)
    {
        return $this->relationValue($model)->first();
    }

    public function setValue($model, $value)
    {
        $value = $this->castValue($value);
		$model->setAttribute($this->getField('id')->name, $value->{$this->to}, true);
		$this->setRelationValue($model, $value);

		return $value;
    }

	protected function setRelationValue($model, $value)
	{
		$model->setRelation($this->name, $value);
	}

    public function relationValue($model)
    {
        return $model->belongsTo($this->on, $this->from, $this->to);
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

        return $query->where($this->getField('id')->attname, $operator, $value);
    }

	public function setFieldValue($model, $field, $value)
	{
		$value = $field->setValue($model, $value);
		$this->setRelationValue($model, $this->castValue($value));

		return $value;
	}

	public function getFieldValue($model, $field, $value)
	{
		return $field->getValue($model, $value);
	}
}
