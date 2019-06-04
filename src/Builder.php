<?php
/**
 * Custom Builder to handle specific functionalities.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore;

use Illuminate\Database\Eloquent\Builder as BuilderBase;
use Illuminate\Support\Str;

class Builder extends BuilderBase
{
    /**
     * Dynamically handle calls into the query or meta instance.
     *
     * @param  mixed $method
     * @param  mixed $parameters
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

        return $this->handleCustomCall($method, $parameters);
    }

    /**
     * Handle where conditions on a specific field.
     *
     * @param  string $method
     * @param  array  $parameters
     * @return mixed
     */
    protected function handleCustomCall(string $method, array $parameters)
    {
        $finder = Str::snake($method);
        $parts = explode('_', $finder);
        $find = false;

        switch ($parts[0]) {
            case 'or':
                $boolean = $parts[0];
            case 'and':
                if ($parts[1] === 'where') {
                    unset($parts[1]);
                } else {
                }

            case 'where':
                unset($parts[0]);

                $boolean = ($boolean ?? 'and');

                break;
        }

        $name = implode($parts, '_');
        $meta = $this->model::getMeta();

        if ($meta->has($name)) {
            $this->where(function ($query) use ($meta, $name, $parameters) {
                return $meta->get($name)->whereValue($query, ...$parameters);
            }, null, null, $boolean);

            if ($find) {
                return $this->first();
            }
        } else {
            $this->forwardCallTo($this->query, $method, $parameters);
        }

        return $this;
    }
}
