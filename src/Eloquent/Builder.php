<?php
/**
 * Custom Builder to handle specific functionalities.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Eloquent;

use Illuminate\Database\Eloquent\Builder as BuilderBase;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Str;
use Laramore\Interfaces\IsProxied;
use MetaManager, Op;

class Builder extends BuilderBase implements IsProxied
{
    /**
     * Add a basic where clause to the query.
     *
     * @param  string|array|\Closure $column
     * @param  mixed                 $operator
     * @param  mixed                 $value
     * @param  string                $boolean
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
            $this->forwardCallTo($this->getQuery(), 'where', \func_get_args());

            return $this;
        }

        $parts = explode('.', $column);

        if (count($parts) === 2) {
            [$table, $column] = $parts;
            $originalTable = $this->getModel()->getTable();

            if ($table !== $originalTable) {
                $modelClass = MetaManager::getForTableName($table)->getModelClass();
                $model = new $modelClass;
                $builder = $model->newEloquentBuilder($this->getQuery())->setModel($model);
                $builder->getQuery()->from($originalTable);

                $this->forwardCallTo($model->registerGlobalScopes($builder), 'where', \func_get_args());

                return $this;
            }
        }

        // If the column is an array, we will assume it is an array of key-value pairs
        // and can add them each as a where clause. We will maintain the boolean we
        // received when the method was called and pass it into the nested where.
        if (is_array($column)) {
            foreach ($column as $attname => $value) {
                $this->where($attname, $value);
            }

            return $this;
        }

        $args = \func_get_args();
        \array_shift($args);

        return \call_user_func([$this, 'where'.Str::studly($column)], ...$args);
    }

    /**
     * Insert a new record into the database.
     *
     * @param  array $values
     * @return boolean
     */
    public function insert($values)
    {
        foreach ($values as $attname => $value) {
            $values[$attname] = $this->dry($attname, $value);
        }

        return $this->toBase()->insert($values);
    }

    /**
     * Insert a new record and get the value of the primary key.
     *
     * @param  array       $values
     * @param  string|null $sequence
     * @return integer
     */
    public function insertGetId($values)
    {
        foreach ($values as $attname => $value) {
            $values[$attname] = $this->dry($attname, $value);
        }

        return $this->toBase()->insertGetId($values);
    }

    /**
     * Dry value with the field.
     *
     * @param  string $attname
     * @param  mixed  $value
     * @return mixed
     */
    protected function dry(string $attname, $value)
    {
        $parts = explode('.', $attname);

        if (count($parts) === 2) {
            [$table, $attname] = $parts;

            return MetaManager::getMetaForTableName($table)->getModelClass()::dry($attname, $value);
        }

        return $this->getModel()::dry($attname, $value);
    }

    /**
     * Handles dynamic "where" clauses to the query.
     *
     * @param  string $method
     * @param  array  $parameters
     * @return self
     */
    public function dynamicWhere(string $where, array $parameters)
    {
        $proxyHandler = $this->getModel()::getProxyHandler();
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
        $methodParts = [];
        $operatorParts = [];
        $segments = \explode('_', Str::title(Str::snake($finder)));

        foreach ($segments as $i => $segment) {
            // We will stack every segment until we think that we got all of them to understand:
            // - the where part,
            // - the attribute name part,
            // - the operator part (if existant).
            if ($segment === 'And' || $segment === 'Or' || $i === (count($segments) - 1)) {
                if ($i === (count($segments) - 1)) {
                    $methodParts[] = $segment;
                }

                do {
                    $method = 'where'.\implode('', $methodParts);

                    // Detect via proxies a whereFieldName method.
                    // By doing that, we can extract the possible operator, which is by default '='.
                    if ($proxyHandler->has($method, $proxyHandler::BUILDER_TYPE)) {
                        if (count($operatorParts)) {
                            $operator = Op::get(Str::camel(\implode('', \array_reverse($operatorParts))));
                        } else {
                            $operator = Op::equal();
                        }

                        if ($operator->needs === 'null') {
                            $value = null;
                        } else {
                            // Only one parameter used.
                            $value = $parameters[$index++];
                        }

                        $params = [$operator, $value, $connector];

                        $this->getModel()::getMeta()->proxyCall($proxyHandler->get($method, $proxyHandler::BUILDER_TYPE), $this, $params);

                        break;
                    }
                } while ($operatorParts[] = \array_pop($methodParts));

                if (count($methodParts)) {
                    $methodParts = [];
                    $operatorParts = [];

                    $connector = \strtolower($segment);
                } else {
                    if (count($operatorParts)) {
                        $operatorName = Str::camel(\implode('', \array_reverse($operatorParts)));

                        if (Op::has($operatorName)) {
                            $this->where($parameters[$index++], Op::get($operatorName), $parameters[$index++] ?? null);

                            return $this;
                        }
                    }

                    throw new \Exception("Where method [$where] is invalid.");
                }
            } else {
                $methodParts[] = $segment;
            }
        }

        return $this;
    }

    /**
     * Dynamically handle calls into the query instance.
     *
     * @param  string $method
     * @param  array  $parameters
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

        if (method_exists($this->model, $scope = 'scope'.ucfirst($method))) {
            return $this->callScope([$this->model, $scope], $parameters);
        }

        if (in_array($method, $this->passthru)) {
            return $this->toBase()->{$method}(...$parameters);
        }

        $proxyHandler = $this->getModel()::getProxyHandler();

        if ($proxyHandler->has($method, $proxyHandler::BUILDER_TYPE)) {
            return $this->getModel()::getMeta()->proxyCall($proxyHandler->get($method, $proxyHandler::BUILDER_TYPE), $this, $parameters);
        }

        if (Str::startsWith($method, ['where', 'orWhere', 'andWhere']) && !\method_exists($this->getQuery(), $method)) {
            return $this->dynamicWhere($method, $parameters);
        }

        $this->forwardCallTo($this->getQuery(), $method, $parameters);

        return $this;
    }
}
