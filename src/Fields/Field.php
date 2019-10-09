<?php
/**
 * Define a basic field.
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
use Laramore\Eloquent\{
    Builder, Model
};
use Laramore\Meta;
use Laramore\Interfaces\IsProxied;
use Laramore\Elements\{
    Type, Operator
};
use Laramore\Validations\Typed;

abstract class Field extends BaseField
{
    protected $attname;

    /**
     * Return the type object of the field.
     *
     * @return Type
     */
    abstract public function getType(): Type;

    /**
     * Return a property by its name.
     *
     * @param  string $key
     * @return mixed
     * @throws \ErrorException If no property exists with this name.
     */
    public function getProperty(string $key, bool $fail=true)
    {
        if ($key === 'type') {
            return $this->getType();
        }

        return parent::getProperty($key, $fail);
    }

    /**
     * Return the main property keys.
     *
     * @return array
     */
    public function getPropertyKeys(): array
    {
        return [
            'nullable', 'default', 'unique'
        ];
    }

    /**
     * Return the main properties.
     *
     * @return array
     */
    public function getProperties(): array
    {
        $properties = [];

        foreach ($this->getPropertyKeys() as $property) {
            $nameKey = explode(':', $property);
            $name = $nameKey[0];
            $key = ($nameKey[1] ?? $name);

            if (\defined($const = 'static::'.\strtoupper(Str::snake($key)))) {
                if ($this->hasRule(\constant($const))) {
                    $properties[$name] = true;
                }
            } else if (!is_null($value = $this->$key)) {
                $properties[$name] = $value;
            }
        }

        return $properties;
    }

    /**
     * Define the name property.
     *
     * @param  string $name
     * @return self
     */
    public function name(string $name)
    {
        parent::name($name);

        // The attribute name is by default the same as the field name.
        if (is_null($this->attname)) {
            $this->attname = Str::snake($name);
        }

        return $this;
    }

    protected function setValidations()
    {
        parent::setValidations();

        // $this->setValidation(Typed::class)->type($this->getType());
    }

    /**
     * Return the query with this field as condition.
     *
     * @param  Builder $builder
     * @param  mixed   ...$args
     * @return Builder
     */
    public function whereNull(Builder $builder, $value=null, $boolean='and', $not=false)
    {
        $builder->getQuery()->whereNull($this->attname, $boolean, $not);

        return $builder;
    }

    /**
     * Return the query with this field as condition.
     *
     * @param  Builder $builder
     * @param  mixed   ...$args
     * @return Builder
     */
    public function whereNotNull(Builder $builder, $value=null, $boolean='and')
    {
        return $this->whereNull($builder, $value, $boolean, true);
    }

    /**
     * Return the query with this field as condition.
     *
     * @param  Builder $builder
     * @param  mixed   ...$args
     * @return Builder
     */
    public function whereIn(Builder $builder, Collection $value=null, $boolean='and', $notIn=false)
    {
        $builder->whereIn($this->attname, $value, $boolean, $notIn);
    }

    /**
     * Return the query with this field as condition.
     *
     * @param  Builder $builder
     * @param  mixed   ...$args
     * @return Builder
     */
    public function whereNotIn(Builder $builder, Collection $value=null, $boolean='and')
    {
        return $this->whereIn($builder, $value, $boolean, true);

        return $builder;
    }

    /**
     * Return the query with this field as condition.
     *
     * @param  Builder $query
     * @param  mixed   ...$args
     * @return Builder
     */
    public function where(Builder $builder, Operator $operator, $value=null, $boolean='and')
    {
        $builder->getQuery()->where($this->attname, $operator, $value, $boolean);

        return $builder;
    }

    /**
     * Return the query with this field as condition.
     *
     * @param  Builder $query
     * @param  mixed   ...$args
     * @return Builder
     */
    public function relate(IsProxied $instance)
    {
        if ($instance instanceof Model) {
            return $this->where($instance, Op::equal(), $instance->getAttribute($this->attname));
        }

        if ($instance instanceof Builder) {
            return $this->where($instance, Op::equal(), $instance->getModel()->getAttribute($this->attname));
        }
    }
}
