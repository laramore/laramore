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

use Illuminate\Support\Collection;
use Laramore\Interfaces\{
    IsProxied, IsALaramoreModel
};

class HasMany extends LinkField
{
    protected $off;
    protected $from;
    protected $on;
    protected $to;

	public function cast($value)
	{
		return $this->transform($value);
	}

	public function dry($value)
	{
		$value = $this->transform($value);

		if ($value instanceof Collection) {
			$value = $value->toArray();
		}

		if (\is_array($value)) {
			return \array_map(function ($value) {
				return isset($value[$this->to]) ? $value[$this->to] : $value;
			}, $value);
		}

		return [isset($value[$this->to]) ? $value[$this->to] : $value];
	}

	public function transform($value)
	{
		if (\is_null($value) || \is_array($value) || $value instanceof Collection) {
			return $value;
		}

		if (!($value instanceof $this->on)) {
			return collect($value);
		}

		$model = new $this->on;
		$model->setRawAttribute($this->to, $value);

		return collect($model);
	}

    public function where($query, $operator=null, $value=null, $boolean='and')
    {
		if (!\is_null($value) && !\is_array($value)) {
			$value = [$value];
		}

		if ($operator === 'one') {
			return $query->whereNested(function ($query) use ($value) {
				foreach ($value as $possibleValue) {
					$query->orWhere($this->from, '=', $possibleValue);
				}
			}, $boolean);
		}

		return $query->whereIn($this->from, $value, $boolean);

    }

    public function retrieve(IsALaramoreModel $model)
    {
        return $this->getOwner()->relateFieldAttribute($this, $model)->getResults();
    }

    public function consume(IsALaramoreModel $model, $value)
    {
        $field = $this->getField('id');
        $field->getOwner()->setFieldAttribute($field, $model, $value[$this->to]);

        return $value;
    }

    public function relate($model)
    {
        return $model->hasMany($this->on, $this->to, $this->from);
    }
}
