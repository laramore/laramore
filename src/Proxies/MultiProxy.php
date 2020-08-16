<?php
/**
 * Groupe multiple proxies and use one of them based on the first argument.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Proxies;

class MultiProxy extends Proxy
{
    /**
     * List of all proxies that this multi proxy can lead.
     *
     * @var array<Proxy>
     */
    protected $proxies = [];

    /**
     * An observer needs at least a name and a Closure.
     *
     * @param string  $name
     * @param boolean $static
     */
    public function __construct(string $name, bool $static=false)
    {
        parent::__construct($name, $name, $static);

        // The first element is either the object on which the proxy is called or its class name, if called statically.
        $this->setCallback(function ($objectOrClass, string $identifierName, ...$args) {
            if (!$this->hasProxy($identifierName)) {
                throw new \BadMethodCallException("The method `{$this->getName()}` does not exist for `$identifierName`");
            }

            return $this->getProxy($identifierName)->__invoke($objectOrClass, ...$args);
        });
    }

    /**
     * Return the field method name that is used for this proxy.
     *
     * @return string
     */
    public function getMethodName(): string
    {
        return $this->getName();
    }

    /**
     * Add a new proxy to this.
     *
     * @param IdentifiedProxy $proxy
     * @return self
     */
    public function addProxy(IdentifiedProxy $proxy)
    {
        if ($this->isStatic() && !$proxy->isStatic()) {
            throw new \LogicException('Multi proxy is statically callable, not the added proxy');
        } else if ($this->isStatic() && !$proxy->isStatic()) {
            throw new \LogicException('Multi proxy is not statically callable, but the added proxy is');
        }

        $this->proxies[$proxy->getIdentifier()] = $proxy;

        return $this;
    }

    /**
     * Indicate if a proxy exists by a field name.
     *
     * @param string $identifier
     * @return boolean
     */
    public function hasProxy(string $identifier): bool
    {
        return isset($this->proxies[$identifier]);
    }

    /**
     * Return a proxy by a field name.
     *
     * @param string $identifier
     * @return Proxy
     */
    public function getProxy(string $identifier): Proxy
    {
        return $this->proxies[$identifier];
    }

    /**
     * Return all proxies listed by this.
     *
     * @return array<Proxy>
     */
    public function getProxies(): array
    {
        return $this->proxies;
    }
}
