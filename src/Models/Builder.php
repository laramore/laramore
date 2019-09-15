<?php
/**
 * Custom Builder to handle specific functionalities.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Models;

use Illuminate\Database\Eloquent\Builder as BuilderBase;
use Illuminate\Support\Str;
use Laramore\Interfaces\IsProxied;

class Builder extends BuilderBase implements IsProxied
{
    /**
     * Add a basic where clause to the query.
     *
     * @param  string|array|\Closure $column
     * @param  mixed                 $operator
     * @param  mixed                 $value
     * @param  string                $boolean
     * @return $this
     */
    public function where($column, $operator=null, $value=null, $boolean='and')
    {
        if ($column instanceof Closure) {
            $column($query = $this->model->newModelQuery());

            $this->query->addNestedWhereQuery($query->getQuery(), $boolean);

            return $this;
        }

        // If the column is an array, we will assume it is an array of key-value pairs
        // and can add them each as a where clause. We will maintain the boolean we
        // received when the method was called and pass it into the nested where.
        if (is_array($column)) {
            foreach ($column as $attname => $value) {
                $column[$attname] = $this->getModel()::dry($attname, $value);
            }

            $this->query->where($column, $operator, $value, $boolean);

            // If the value is a Closure, it means the developer is performing an entire
            // sub-select within the query and we will need to compile the sub-select
            // within the where clause to get the appropriate query record results.
        } else if ($value instanceof Closure) {
            $this->query->where($column, $operator, $value, $boolean);
        } else if (func_num_args() === 2) {
            $this->query->where($column, $this->getModel()::dry($column, $operator));
        } else {
            $this->query->where($column, $operator, $this->getModel()::dry($column, $value), $boolean);
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

        $this->forwardCallTo($this->query, $method, $parameters);

        return $this;
    }
}
