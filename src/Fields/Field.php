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

use Illuminate\Support\Str;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laramore\Models\{
    Builder, Model
};
use Laramore\{
    Meta, Type
};
use Laramore\Interfaces\IsProxied;

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
            $this->attname = $name;
        }

        return $this;
    }

    /**
     * Define a default value for this field.
     *
     * @param  mixed $value
     * @return self
     */
    public function default($value=null)
    {
        $this->needsToBeUnlocked();

        if (is_null($value)) {
            $this->nullable();
        }

        $this->defineProperty('default', $value);

        return $this;
    }

    /**
     * Return the query with this field as condition.
     *
     * @param  QueryBuilder $query
     * @param  mixed   ...$args
     * @return QueryBuilder
     */
    public function where(QueryBuilder $query, $operator=null, $value=null, $boolean='and')
    {
        return $query->where($this->attname, $operator, $value, $boolean);
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
            return $this->getOwner()->whereFieldAttribute($this, $instance, $instance->getAttribute($this->attname));
        }

        if ($instance instanceof Builder) {
            return $this->getOwner()->whereFieldAttribute($this, $instance, $instance->getModel()->getAttribute($this->attname));
        }
    }
}
