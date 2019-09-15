<?php
/**
 * Create an Observer to add a \Closure on a specific model event.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Proxies;

use Laramore\Fields\BaseField;
use Laramore\Traits\IsLocked;
use Closure;

class MultiProxy extends BaseProxy
{
    /**
     * An observer needs at least a name and a Closure.
     *
     * @param string  $name
     * @param integer $priority
     * @param array   $data
     */
    public function __construct(string $name, array $data=[])
    {
        parent::__construct($name, $name, $data);
    }

    public function getMethodName()
    {
        return $this->getName();
    }

    /**
     * Actions during locking.
     *
     * @return void
     */
    protected function locking()
    {
        $this->setCallback(function (string $fieldName, ...$args) {
            return $this->getProxy($fieldName)->getCallback()(...$args);
        });

        parent::locking();
    }
}
