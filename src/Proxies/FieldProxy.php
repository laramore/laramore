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

class FieldProxy extends BaseProxy
{
    protected $field;

    /**
     * An observer needs at least a name and a Closure.
     *
     * @param string  $name
     * @param integer $priority
     * @param array   $data
     */
    public function __construct(string $name, BaseField $field, string $methodName, array $injections=[], array $data=[])
    {
        $this->setField($field);

        parent::__construct($name, $methodName, $injections, $data);
    }

    /**
     * Define the proxy field.
     *
     * @param BaseField $field
     * @return self
     */
    public function setField(BaseField $field)
    {
        $this->needsToBeUnlocked();

        $this->field = $field;

        return $this;
    }

    public function getField()
    {
        return $this->field;
    }

    /**
     * Actions during locking.
     *
     * @return void
     */
    protected function locking()
    {
        $this->setCallback(Closure::fromCallable([$this->getField(), $this->getMethodName()]));

        parent::locking();
    }
}
