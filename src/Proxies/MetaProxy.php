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
use Laramore\Meta;
use Closure;

class MetaProxy extends BaseProxy
{
    protected $meta;
    protected $field;

    /**
     * An observer needs at least a name and a Closure.
     *
     * @param string  $name
     * @param integer $priority
     * @param array   $data
     */
    public function __construct(string $name, Meta $meta, BaseField $field, string $methodName, array $injections=[], array $data=[])
    {
        parent::__construct($name, $methodName, $injections, $data);

        $this->setMeta($meta);
        $this->setField($field);
        $this->setCallback(Closure::fromCallable([$this->getMeta(), $this->getMethodName()]));
    }

    /**
     * Define the proxy field.
     *
     * @param BaseField $field
     * @return self
     */
    public function setMeta(Meta $meta)
    {
        $this->needsToBeUnlocked();

        $this->meta = $meta;

        return $this;
    }

    public function getMeta()
    {
        return $this->meta;
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
}
