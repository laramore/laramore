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

        return $this->handleCustomCall($method, $parameters);
    }

    protected function handleCustomCall($method, $parameters)
    {
        $finder = Str::snake($method);
        $parts = explode('_', $finder);
        $find = true;

        switch ($parts[0]) {
            case 'where':
                $boolean = 'and';
                $find = false;
            case 'or':
                $boolean = ($boolean ?? $parts[0]);
            case 'and':
                if ($parts[1] === 'where') {
                    $find = false;
                    unset($parts[1]);
                }

                unset($parts[0]);

            default:
                $name = implode($parts, '_');
                $boolean = ($boolean ?? 'and');
                break;
        }

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
