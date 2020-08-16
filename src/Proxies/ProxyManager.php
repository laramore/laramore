<?php
/**
 * Handle all proxy handlers.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Proxies;

use Illuminate\Container\Container;
use Illuminate\Support\Arr;
use Laramore\Contracts\{
    Manager\LaramoreManager, Configured, Proxied
};
use Laramore\Observers\{
    BaseManager, BaseHandler
};

class ProxyManager extends BaseManager implements LaramoreManager, Configured
{
    /**
     * Allowed observable sub class.
     *
     * @var string
     */
    protected $managedClass = Proxied::class;

    /**
     * The observable handler class to generate.
     *
     * @var string
     */
    protected $handlerClass = ProxyHandler::class;

    /**
     * Common proxies between all handlers.
     *
     * @var array<Proxy>
     */
    protected $commonProxies = [];

    /**
     * Generate common proxy.
     */
    public function __construct()
    {
        $class = $this->getConfig('class');

        foreach ($this->getConfig('configurations') as $methodName => $data) {
            if (\is_null($data)) {
                continue;
            }

            $this->commonProxies[] = $proxy = new $class(
                $methodName,
                $methodName,
                Arr::get($data, 'static', false),
                Arr::get($data, 'allow_multi', true),
            );

            $proxy->setCallback(Arr::get($data, 'callback', $methodName));
        }
    }

    /**
     * Return the configuration path for this field.
     *
     * @param string $path
     * @return mixed
     */
    public function getConfigPath(string $path=null)
    {
        return 'proxy'.(\is_null($path) ? '' : ".$path");
    }

    /**
     * Return the configuration for this field.
     *
     * @param string $path
     * @param mixed  $default
     * @return mixed
     */
    public function getConfig(string $path=null, $default=null)
    {
        return Container::getInstance()->config->get($this->getConfigPath($path), $default);
    }

    /**
     * Return all common proxies.
     *
     * @return array<Proxy>
     */
    public function getCommonProxies(): array
    {
        return $this->commonProxies;
    }

    /**
     * Create an Handler for a specific observable class.
     * Add all common proxies.
     *
     * @param  string $observableClass
     * @return BaseHandler|ProxyHandler
     */
    public function createHandler(string $observableClass): BaseHandler
    {
        $handler = parent::createHandler($observableClass);

        foreach ($this->getCommonProxies() as $proxy) {
            $handler->add($proxy);
        }

        return $handler;
    }
}
