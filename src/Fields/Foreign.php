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

use Illuminate\Support\{
    Str, Collection
};
use Laramore\Elements\Operator;
use Laramore\Eloquent\Builder;
use Laramore\Fields\Link\LinkField;
use Laramore\Interfaces\{
    IsALaramoreModel, IsProxied
};

class Foreign extends CompositeField
{
    protected $on;
    protected $to;
    protected $off;
    protected $from;
    protected $reversedName;

    protected static $defaultFields = [
        'id' => [Number::class, (Increment::DEFAULT_INCREMENT | Number::DEFAULT_FIELD)],
    ];
    protected static $defaultLinks = [
        'reversed' => Link\HasMany::class,
    ];

    public function getReversed(): LinkField
    {
        return $this->getLink('reversed');
    }

    public function on(string $model, string $reversedName=null)
    {
        $this->needsToBeUnlocked();

        if ($model === 'self') {
            $this->defineProperty('on', $model);
        } else {
            $this->defineProperty('on', $this->getReversed()->off = $model);
            $this->to($this->getReversed()->off::getMeta()->getPrimary()->attname);
        }

        if ($reversedName) {
            $this->reversedName($reversedName);
        }

        return $this;
    }

    public function onSelf()
    {
        return $this->on('self');
    }

    public function to(string $name)
    {
        $this->needsToBeUnlocked();

        $this->defineProperty('to', $this->getReversed()->from = $name);

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
        if ($this->on === 'self') {
            $this->on($this->getMeta()->getModelClass());
        }

        parent::owned();

        $this->defineProperty('off', $this->getReversed()->on = $this->getMeta()->getModelClass());
        $this->defineProperty('from', $this->getReversed()->to = $this->getField('id')->attname);
    }

    protected function checkRules()
    {
        if (!$this->on) {
            throw new \Exception('Related model settings needed. Set it by calling `on` method');
        }

        $this->defineProperty('reversedName', $this->getReversed()->name);

        parent::checkRules();
    }

    public function isOnSelf()
    {
        return $this->on === $this->getMeta()->getModelClass();
    }

    public function cast($value)
    {
        return $this->transform($value);
    }

    public function dry($value)
    {
        $value = $this->transform($value);

        return isset($value[$this->to]) ? $value[$this->to] : $value;
    }

    public function transform($value)
    {
        if (\is_null($value) || $value instanceof $this->on || \is_array($value) || $value instanceof Collection) {
            return $value;
        }

        $model = new $this->on;
        $model->setRawAttribute($this->to, $value);

        return $model;
    }

    public function retrieve(IsALaramoreModel $model)
    {
        return $this->relate($model)->getResults();
    }

    public function consume(IsALaramoreModel $model, $value)
    {
        $model->setAttribute($this->getField('id')->attname, $value[$this->to]);

        return $value;
    }

    /**
     * Return the query with this field as condition.
     *
     * @param  Builder $query
     * @param  mixed   ...$args
     * @return Builder
     */
    public function relate(IsProxied $model)
    {
        return $model->belongsTo($this->on, $this->from, $this->to);
    }

    public function reverbate(IsALaramoreModel $model, $value): bool
    {
        return $value->save();
    }

    public function whereNull(Builder $builder, $value=null, $boolean='and', $not=false)
    {
        $builder->getQuery()->whereNull($this->attname, $boolean, $not);

        return $builder;
    }

    public function whereNotNull(Builder $builder, $value=null, $boolean='and')
    {
        return $this->whereNull($builder, $value, $boolean, true);

        return $builder;
    }

    public function whereIn(Builder $builder, Collection $value=null, $boolean='and', $not=false)
    {
        $builder->getQuery()->whereIn($this->getField('id')->attname, $value, $boolean, $not);

        return $builder;
    }

    public function whereNotIn(Builder $builder, Collection $value=null, $boolean='and')
    {
        return $this->whereIn($builder, $value, $boolean, true);
    }

    public function where(Builder $builder, Operator $operator=null, $value=null, $boolean='and')
    {
        if ($operator->needs === 'collection') {
            return $this->whereIn($builder, $value, $boolean, ($operator === Op::notIn()));
        }

        $builder->getQuery()->where($this->getField('id')->attname, $operator, $value, $boolean);

        return $builder;
    }
}
