<?php
/**
 * Create an Observer to add a \Closure on a specific model event.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Proxies;

use Illuminate\Support\Arr;
use Laramore\Contracts\Proxied;
use Laramore\Observers\BaseObserver;

class Proxy extends BaseObserver
{
    /**
     * The method to call.
     *
     * @var string
     */
    protected $methodName;

    /**
     * Static proxy.
     *
     * @var bool
     */
    protected $static;

    /**
     * Allow multi proxy.
     *
     * @var bool
     */
    protected $multi;

    /**
     * An observer needs at least a name and a Closure.
     *
     * @param string  $name
     * @param string  $methodName
     * @param boolean $static
     * @param boolean $allowMulti
     */
    public function __construct(string $name, string $methodName, bool $static=false, bool $allowMulti=true)
    {
        $this->setMethodName($methodName);
        $this->setStatic($static);
        $this->allowMulti($allowMulti);

        parent::__construct($name, null, self::MEDIUM_PRIORITY, []);
    }

    /**
     * Define the method name that is used for this proxy.
     *
     * @param string $methodName
     * @return self
     */
    public function setMethodName(string $methodName)
    {
        $this->needsToBeUnlocked();

        $this->methodName = $methodName;

        return $this;
    }

    /**
     * Return the method name that is used for this proxy.
     *
     * @return string
     */
    public function getMethodName(): string
    {
        return $this->methodName;
    }

    /**
     * Allow this proxy to be in a multi proxy.
     *
     * @param boolean $allowMulti
     * @return self
     */
    public function allowMulti(bool $allowMulti)
    {
        $this->needsToBeUnlocked();

        $this->allowMulti = $allowMulti;

        return $this;
    }

    /**
     * Indicate if this proxy allows to be in a multi proxy.
     *
     * @return boolean
     */
    public function allowsMulti(): bool
    {
        return $this->allowMulti;
    }

    /**
     * Set this proxy as static or not.
     *
     * @param boolean $static
     * @return self
     */
    public function setStatic(bool $static)
    {
        $this->needsToBeUnlocked();

        $this->static = $static;

        return $this;
    }

    /**
     * Return if this proxy is static or not.
     *
     * @return boolean
     */
    public function isStatic(): bool
    {
        return $this->static;
    }

    /**
     * Check if arguments for the invocation are valid.
     *
     * @param array $args
     * @return void
     */
    protected function checkArguments(array $args)
    {
        if ($this->isStatic() && !is_string(Arr::get($args, 0))) {
            throw new \BadMethodCallException("The proxy `{$this->getName()}` must be called statically.");
        }

        if (!$this->isStatic() && !(Arr::get($args, 0) instanceof Proxied)) {
            throw new \BadMethodCallException("The proxy `{$this->getName()}` cannot be called statically.");
        }
    }

    /**
     * Call the proxy.
     *
     * @param  mixed ...$args
     * @return mixed
     */
    public function __invoke(...$args)
    {
        $this->checkArguments($args);

        return \call_user_func($this->getCallback(), ...$args);
    }
}
