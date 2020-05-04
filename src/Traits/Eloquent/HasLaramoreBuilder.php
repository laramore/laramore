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

trait HasLaramoreBuilder
{
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

        return $builder->getModel()->newCollection($models)->fetching(false);
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
            $column($query = $this->model->newModelQuery());

            $this->query->addNestedWhereQuery($query->getQuery(), 'and');

            return $this;
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
        if (is_array($column)) {
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

        $args = \func_get_args();
        \array_shift($args);

        return \call_user_func([$this, 'where' . Str::studly($column)], ...$args);
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
                $meta = Meta::getMetaForTableName($table);
                $model = $meta->getModelClass();
            } else {
                $attname = $key;
                $model = $this->getModel();
                $meta = $model::getMeta();
            }

            if ($meta->hasField($attname, AttributeField::class)) {
                $values[$key] = $model::dry($attname, $value);
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

            return Meta::getMetaForTableName($table)->getModelClass()::dry($attname, $value);
        }

        return $this->getModel()::dry($attname, $value);
    }

    /**
     * Insert a new record and get the value of the primary key.
     *
     * @param  array|mixed $values
     * @return integer
     */
    public function insertGetId($values)
    {
        $this->getModel()->fill($values);

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
        $this->getModel()->fill($values);

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
        $this->getModel()->fill($values);

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
        $connector = 'and';

        if (Str::startsWith($where, 'orWhere')) {
            $finder = \substr($where, 7);
            $connector = 'or';
        } else if (Str::startsWith($where, 'andWhere')) {
            $finder = \substr($where, 8);
        } else {
            $finder = \substr($where, 5);
        }

        $index = 0;
        $lastMethod = null;
        $nameParts = [];
        $operatorParts = [];
        $segments = \explode('_', Str::title(Str::snake($finder)));

        foreach ($segments as $i => $segment) {
            // We will stack every segment until we think that we got all of them to understand:
            // - the where part,
            // - the attribute name part,
            // - the operator part (if existant else it is equal to '=').
            if ($segment === 'And' || $segment === 'Or' || $i === (\count($segments) - 1)) {
                if ($i === (\count($segments) - 1)) {
                    $nameParts[] = $segment;
                }

                do {
                    $method = 'where' . \implode('', $nameParts);

                    // Detect via proxies a whereFieldName method.
                    // By doing that, we can extract the possible operator, which is by default '='.
                    if ($this->getModel()::getMeta()->getProxyHandler()->has('scope' . \ucfirst($method))) {
                        if (\count($operatorParts)) {
                            $opName = Str::snake(\implode('', \array_reverse($operatorParts)));

                            if (Operator::has($opName)) {
                                $operator = Operator::get($opName);
                            } else if (Str::startsWith($opName, 'is_') && Operator::has($subOpName = substr($opName, 3))) {
                                $operator = Operator::get($subOpName);
                            } else if (Str::startsWith($opName, 'are_') && Operator::has($subOpName = substr($opName, 4))) {
                                $operator = Operator::get(substr($opName, 4));
                            } else {
                                throw new \Exception("Operator `$opName` not identified");
                            }
                        } else {
                            $operator = Operator::equal();
                        }

                        if ($operator->needs === 'null') {
                            $value = null;
                        } else {
                            if (!isset($parameters[$index])) {
                                throw new \Exception("Missing value for operator `{$operator->getName()}`");
                            }

                            // Only one parameter used.
                            $value = $parameters[$index++];
                        }

                        $params = [$operator, $value, $connector];

                        if (\count($nameParts) === 0) {
                            if (\is_null($lastMethod)) {
                                $format = '`where{field name}{(operator)}`';
                                throw new \Exception('A dynamic where condition is composed like this: '.$format);
                            }

                            $method = $lastMethod;
                            $nameParts[] = $lastMethod;
                        } else {
                            $lastMethod = $method;
                        }

                        $this->__proxy($method, $params);

                        break;
                    }
                } while ($operatorParts[] = \array_pop($nameParts));

                if (\count($nameParts)) {
                    $nameParts = [];
                    $operatorParts = [];

                    $connector = \strtolower($segment);
                } else if (\count($operatorParts)) {
                    $operatorName = Str::camel(\implode('', \array_reverse($operatorParts)));

                    if (Operator::has($operatorName)) {
                        $this->where(
                            $parameters[$index++],
                            Operator::get($operatorName),
                            ($parameters[$index++] ?? null)
                        );

                        return $this;
                    }
                } else {
                    throw new \Exception("Where method `$where` is invalid");
                }
            } else {
                $nameParts[] = $segment;
            }
        }

        return $this;
    }

    /**
     * Multiple where conditions
     *
     * @param array        $column
     * @param mixed        $operator
     * @param mixed        $value
     * @param string|mixed $boolean
     * @return self
     */
    public function multiWhere(array $column, $operator=null, $value=null, $boolean='and')
    {
        if (Arr::isAssoc($column)) {
            foreach ($column as $attname => $value) {
                $this->where($attname, $operator, $value);
            }
        } else {
            if (\func_num_args() === 2) {
                [$operator, $value] = [$value, $operator];
            }

            if (\is_array($value) && !\is_object($value)) {
                if (Arr::isAssoc($value)) {
                    foreach ($column as $attname) {
                        $this->where($attname, $operator, $value[$attname], $boolean);
                    }
                } else {
                    foreach ($column as $index => $attname) {
                        $this->where($attname, $operator, $value[$index], $boolean);
                    }
                }
            } else {
                foreach ($column as $attname) {
                    $this->where($attname, $operator, $value, $boolean);
                }
            }
        }

        return $this;
    }

    /**
     * Call a proxy by its name.
     * All proxies are handled in models.
     * Query builders only calls methods from models via 'scope' names.
     *
     * @param mixed $name
     * @param mixed $args
     * @return mixed
     */
    public function __proxy($name, $args)
    {
        return $this->getModel()::getMeta()->getProxyHandler()->get('scope' . \ucfirst($name))->__invoke($this, ...$args);
    }

    /**
     * Return a static proxy by its name.
     *
     * @param mixed $name
     * @param mixed $args
     * @return mixed
     */
    public static function __proxyStatic($name, $args)
    {
        throw new \BadMethodCallException("The proxy `$name` cannot be called statically.");
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

        if (isset($this->localMacros[$method])) {
            array_unshift($parameters, $this);

            return $this->localMacros[$method](...$parameters);
        }

        if (isset(static::$macros[$method])) {
            if (static::$macros[$method] instanceof Closure) {
                return call_user_func_array(static::$macros[$method]->bindTo($this, static::class), $parameters);
            }

            return call_user_func_array(static::$macros[$method], $parameters);
        }

        $method = Str::camel($method);
        $scope = 'scope' . ucfirst($method);

        if (method_exists($this->model, $scope)) {
            return $this->callScope([$this->model, $scope], $parameters);
        }

        if ($this->getModel()::getMeta()->getProxyHandler()->has($scope)) {
            return $this->__proxy($method, $parameters);
        }

        if (in_array($method, $this->passthru)) {
            return $this->toBase()->{$method}(...$parameters);
        }

        if (Str::startsWith($method, ['where', 'orWhere', 'andWhere']) && !\method_exists($this->getQuery(), $method)) {
            return $this->dynamicWhere($method, $parameters);
        }

        if (\version_compare(app()::VERSION, '5.7.0', '<')) {
            $this->query->{$method}(...$parameters);
        } else {
            $this->forwardCallTo($this->getQuery(), $method, $parameters);
        }

        return $this;
    }
}
