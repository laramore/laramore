<?php

namespace Laramore\Fields;

use Laramore\Meta;

abstract class LinkField extends BaseField
{
    public function getDefaultProperties(): array
    {
        return [
            'type' => 'link',
        ];
    }

    public static function link(...$args)
    {
        return new static(...$args);
    }

    protected function owning()
    {
        if (!($this->getOwner() instanceof Meta) && !($this->getOwner() instanceof CompositeField)) {
            throw new \Exception('The link field should be owned by a CompositeField');
        }
    }

    protected function locking()
    {
        if ($this->hasProperty('attname')) {
            throw new \Exception('The attribute name property cannot be set for a link field');
        }
    }

    public function castValue($value)
    {
        return $value;
    }

    public function getValue($model, $value)
    {
        return $this->castValue($value);
    }

    public function setValue($model, $value)
    {
        $value = $this->castValue($value);

        return $value;
    }

    public function relationValue($model)
    {
        return $this->whereValue($model, $model->{$this->name});
    }

    public function whereValue($query, ...$args)
    {
        return $query->where($this->name, ...$args);
    }
}