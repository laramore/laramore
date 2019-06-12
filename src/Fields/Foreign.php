<?php
/**
 * Define a foreign field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

use Illuminate\Support\Str;

class Foreign extends CompositeField
{
    protected $on;
    protected $to;
    protected $off;
    protected $from;
    protected $reversedName;

    protected static $defaultFields = [
        'id' => [Number::class, (Increment::DEFAULT_INCREMENT | Number::FILLABLE)],
    ];
    protected static $defaultLinks = [
        'reversed' => Link\HasMany::class,
    ];

    public function on(string $model, string $reversedName=null)
    {
        $this->needsToBeUnlocked();

        $this->defineProperty('on', $this->getLink('reversed')->off = $model);
        $this->to($this->getLink('reversed')->off::getMeta()->getPrimary()->attname);

        $this->reversedName($reversedName);

        return $this;
    }

    public function to(string $name)
    {
        $this->needsToBeUnlocked();

        $this->defineProperty('to', $this->getLink('reversed')->from = $name);

        return $this;
    }

    public function reversedName(string $reversedName=null)
    {
        $this->needsToBeUnlocked();

        $this->linksName['reversed'] = $reversedName ?: '*{modelname}';

        return $this;
    }

    public function owned()
    {
        $this->defineProperty('off', $this->getLink('reversed')->on = $this->getModelClass());

        parent::owned();

        $this->defineProperty('from', $this->getLink('reversed')->to = $this->getField('id')->attname);
    }

    protected function locking()
    {
        if (!$this->on) {
            throw new \Exception('Related model settings needed. Set it by calling `on` method');
        }

        $this->defineProperty('reversedName', $this->getLink('reversed')->name);

        parent::locking();
    }

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
        return $this->relationValue($model)->first();
    }

    public function setValue($model, $value)
    {
        $value = $this->castValue($model, $value);
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
        $this->setRelationValue($model, $this->castValue($model, $value));

        return $value;
    }

    public function getFieldValue($model, $field, $value)
    {
        return $field->getValue($model, $value);
    }
}
