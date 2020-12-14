<?php
/**
 * Inject in builder Laramore management.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Traits\Eloquent;

use Illuminate\Database\Query\Expression;
use Illuminate\Support\Str;
use Laramore\Facades\{
    Meta, Operator
};
use Closure;
use Illuminate\Support\Arr;
use Laramore\Contracts\Field\AttributeField;
use Laramore\Elements\OperatorElement;

trait HasLaramoreBuilder
{
    /**
     * All of the globally registered builder macros.
     *
     * @var array
     */
    protected static $macros = [];

    /**
     * Create a new instance of the model being queried.
     *
     * @param  array|mixed $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function newModelInstance($attributes=[])
    {
        $model = $this->model->newInstance($attributes, true)->setConnection(
            $this->query->getConnection()->getName()
        );
        $model->fetchingDatabase = false;

        return $model;
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array|mixed $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function get($columns=['*'])
    {
        $builder = $this->applyScopes();

        // If we actually found models we will also eager load any relationships that
        // have been specified as needing to be eager loaded, which will solve the
        // n+1 query issue for the developers to avoid running a lot of queries.
        if (count($models = $builder->getModels($columns)) > 0) {
            $models = $builder->eagerLoadRelations($models);
        }

        return $builder->getModel()->newCollection($models)->fetchingDatabase(false);
    }

    /**
     * Handle nested condition.
     *
     * @param \Closure|callback $callback
     * @param string|mixed      $boolean
     * @return self
     */
    public function whereNested($callback, $boolean='and')
    {
        \call_user_func($callback, $query = $this->model->newModelQuery());

        $this->query->addNestedWhereQuery($query->getQuery(), $boolean);

        return $this;
    }

    /**
     * Add a basic where clause to the query.
     *
     * @param  string|array|\Closure $column
     * @param  mixed                 $operator
     * @param  mixed                 $value
     * @param  mixed|string          $boolean
     * @return self
     */
    public function where($column, $operator=null, $value=null, $boolean='and')
    {
        if ($column instanceof Closure) {
            return $this->whereNested(...\func_get_args());
        }

        if ($column instanceof Expression) {
            if (\version_compare(app()::VERSION, '5.7.0', '<')) {
                $this->query->where(...\func_get_args());
            } else {
                $this->forwardCallTo($this->getQuery(), 'where', \func_get_args());
            }

            return $this;
        }

        // If the column is an array, we will assume it is an array of key-value pairs
        // and can add them each as a where clause. We will maintain the boolean we
        // received when the method was called and pass it into the nested where.
        if (\is_array($column)) {
            $this->multiWhere(...\func_get_args());
        }

        $parts = explode('.', $column);

        if (\count($parts) === 2) {
            [$table, $column] = $parts;
            $originalTable = $this->getModel()->getTable();

            if ($table !== $originalTable) {
                $modelClass = Meta::getForTableName($table)->getModelClass();
                $model = new $modelClass;
                $builder = $model->newEloquentBuilder($this->getQuery())->setModel($model);
                $builder->getQuery()->from($originalTable);

                if (\version_compare(app()::VERSION, '5.7.0', '<')) {
                    $model->registerGlobalScopes($builder)->where(...\func_get_args());
                } else {
                    $this->forwardCallTo($model->registerGlobalScopes($builder), 'where', \func_get_args());
                }

                return $this;
            }
        }

        $meta = $this->getModel()::getMeta();

        $args = \func_get_args();
        \array_shift($args);

        if ($meta->hasField($column)) {
            $field = $meta->getField($column);

            return $field->getOwner()->whereFieldValue($field, $this, ...$args);
        }

        return \call_user_func([$this, 'where'.Str::studly($column)], ...$args);
    }

    /**
     * Dry values.
     *
     * @param  array $values
     * @return mixed
     */
    public function dryValues(array $values)
    {
        foreach ($values as $key => $value) {
            $parts = explode('.', $key);

            if (\count($parts) === 2) {
                [$table, $attname] = $parts;
                $meta = Meta::getForTableName($table);
                $model = $meta->getModelClass();
            } else {
                $attname = $key;
                $model = $this->getModel();
                $meta = $model::getMeta();
            }

            if ($meta->hasField($attname, AttributeField::class)) {
                $values[$key] = $meta->getField($attname)->dry($value);
            } else {
                unset($values[$key]);
            }
        }

        return $values;
    }

    /**
     * Dry value with the field.
     *
     * @param  string $attname
     * @param  mixed  $value
     * @return mixed
     */
    public function dry(string $attname, $value)
    {
        $parts = explode('.', $attname);

        if (\count($parts) === 2) {
            [$table, $attname] = $parts;

            $meta = Meta::getForTableName($table);
        } else {
            $meta = $this->getModel()::getMeta();
        }

        return $meta->getField($attname, AttributeField::class)->dry($value);
    }

    /**
     * Insert a new record and get the value of the primary key.
     *
     * @param  array|mixed $values
     * @return integer
     */
    public function insertGetId($values)
    {
        $this->getModel()->fetchingDatabase = true;
        $this->getModel()->fill($values);
        $this->getModel()->fetchingDatabase = false;

        return $this->toBase()->insertGetId($this->dryValues($this->getModel()->getAttributes()));
    }

    /**
     * Insert a new record into the database.
     *
     * @param  array|mixed $values
     * @return boolean
     */
    public function insert($values)
    {
        $this->getModel()->fetchingDatabase = true;
        $this->getModel()->fill($values);
        $this->getModel()->fetchingDatabase = false;

        return $this->toBase()->insert($this->dryValues($this->getModel()->getAttributes()));
    }

    /**
     * Update a record in the database.
     *
     * @param  array $values
     * @return integer
     */
    public function update(array $values)
    {
        $values = $this->addUpdatedAtColumn($values);
        $model = $this->getModel();

        $model->fetchingDatabase = true;
        $model->fill($values);
        $model->fetchingDatabase = false;

        return $this->toBase()->update($this->dryValues(\array_merge(
            $values,
            $this->getModel()->getDirty()
        )));
    }

    /**
     * Handles dynamic "where" clauses to the query.
     *
     * @param  string $where
     * @param  array  $parameters
     * @return self
     */
    public function dynamicWhere(string $where, array $parameters)
    {
        $boolean = 'and';

        if (Str::startsWith($where, 'orWhere')) {
            $boolean = 'or';
            $method = \substr($where, 7);
        } else if (Str::startsWith($where, 'andWhere')) {
            $method = \substr($where, 8);
        } else {
            $method = \substr($where, 5);
        }

        $meta = $this->getModel()::getMeta();
        $columns = [];
        $operators = [];
        $booleans = [];

        $parts = \explode('_', Str::lower(Str::snake($method)));
        $nextParts = [];

        if (\count($parts) === 1 && \count($parameters) && Operator::has($parts[0])) {
            return $this->where(\array_shift($parameters), $parts[0], (\array_shift($parameters) ?? null), $boolean);
        }

        do {
            $name = implode('_', $parts);

            if ($meta->hasField($name)) {
                $operator = null;
                $operatorParts = $nextParts;
                $nextParts = [];

                do {
                    $operatorName = implode('_', $operatorParts);

                    dump($operatorName);
                    if (Operator::has($operatorName)) {
                        $operator = Operator::get($operatorName);

                        break;
                    }

                    $subPart = \array_pop($operatorParts);
                    if (!\is_null($subPart)) {
                        \array_unshift($nextParts, $subPart);
                    }
                } while ($subPart);

                if (\is_null($operator)) {
                    if (\count($nextParts) && !\in_array($nextParts[0], ['or', 'and'])) {
                        $operator = \implode('_', $nextParts);

                        throw new \Exception("The operator `$operator` was not recognized for field `$name`.");
                    }

                    $operator = Operator::equal();
                }

                $columns[] = $name;
                $operators[] = $operator;
                $booleans[] = $boolean;

                if (\count($nextParts) === 1) {
                    throw new \Exception("Cannot end dynamic where with `{$nextParts[0]}`");
                }

                // Prepare next boolean connector.
                $boolean = \array_shift($nextParts) ?: 'and';

                if (!\in_array($boolean, ['or', 'and'])) {
                    throw new \Exception("The value `$boolean` is not a boolean for `$name` where.");
                }

                $parts = $nextParts;
                $nextParts = [];
                $part = true;
                // Indicate that we continue.

                continue;
            }

            $part = \array_pop($parts);
            if (!\is_null($part)) {
                \array_unshift($nextParts, $part);
            }
        } while ($part);

        if (\count($nextParts)) {
            $part = implode('_', $nextParts);

            throw new \Exception("Dynamic where was not able to understand `$part`");
        }

        if (\count($columns) !== \count($parameters)) {
            throw new \Exception('They are more values than columns in dynamic where.');
        }

        return $this->multiWhere($columns, $operators, $parameters, $booleans);
    }

    /**
     * Multiple where conditions
     *
     * @param array        $column
     * @param string|array $operator
     * @param string|array $value
     * @param string|array $boolean
     * @return self
     */
    public function multiWhere(array $column, $operator=null, $value=null, $boolean='and')
    {
        if (Arr::isAssoc($column)) {
            return $this->multiWhere(\array_keys($column), $operator, \array_values($value), $boolean);
        }

        if (\func_num_args() === 2) {
            [$operator, $value] = [$value, $operator];
        }

        if (\is_array($value) && !\is_object($value)) {
            $valuesAssoc = Arr::isAssoc($value);

            foreach ($column as $index => $attname) {
                $subValue = $valuesAssoc ? $value[$attname] : $value[$index];
                $subOperator = \is_array($operator) ? $operator[$index] : $operator;
                $subBoolean = \is_array($boolean) ? $boolean[$index] : $boolean;

                $this->where($attname, $subOperator, $subValue, $subBoolean);
            }
        } else {
            foreach ($column as $index => $attname) {
                $subOperator = \is_array($operator) ? $operator[$index] : $operator;
                $subBoolean = \is_array($boolean) ? $boolean[$index] : $boolean;

                $this->where($attname, $subOperator, $value, $subBoolean);
            }
        }

        return $this;
    }

    /**
     * Dynamically handle calls into the query instance.
     *
     * @param  string|mixed $method
     * @param  array|mixed  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if ($method === 'macro') {
            $this->localMacros[$parameters[0]] = $parameters[1];

            return;
        }

        if ($this->hasMacro($method)) {
            array_unshift($parameters, $this);

            return $this->localMacros[$method](...$parameters);
        }

        if (static::hasGlobalMacro($method)) {
            $callable = static::$macros[$method];

            if ($callable instanceof Closure) {
                $callable = $callable->bindTo($this, static::class);
            }

            return $callable(...$parameters);
        }

        if ($this->hasNamedScope($method)) {
            return $this->callNamedScope($method, $parameters);
        }

        if (in_array($method, $this->passthru)) {
            return $this->toBase()->{$method}(...$parameters);
        }

        if (Str::startsWith($method, ['where', 'orWhere', 'andWhere'])) {
            return $this->dynamicWhere($method, $parameters);
        }

        $this->forwardCallTo($this->query, $method, $parameters);

        return $this;
    }
}
