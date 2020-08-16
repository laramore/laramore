<?php
/**
 * Handle all observers for a specific model.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Proxies;

use Illuminate\Container\Container;
use Laramore\Contracts\Configured;
use Laramore\Observers\{
    BaseHandler, BaseObserver
};

class ProxyHandler extends BaseHandler implements Configured
{
    /**
     * The observable class.
     *
     * @var string
     */
    protected $observerClass = Proxy::class;

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
        return config($this->getConfigPath($path), $default);
    }

    /**
     * Add an observer to a list of observers.
     *
     * @param BaseObserver     $proxy
     * @param array<BaseProxy> $proxies
     * @return self
     */
    protected function push(BaseObserver $proxy, array &$proxies)
    {
        parent::push($proxy, $proxies);

        if (!($proxy instanceof IdentifiedProxy)) {
            return $this;
        }

        if (!$proxy->allowsMulti()) {
            return $this;
        }

        $name = $proxy->getMultiName();
        $class = $this->getConfig('multi_class');

        if ($this->has($name)) {
            $multiProxy = $this->get($name);

            if (!($multiProxy instanceof $class)) {
                throw new \LogicException("Conflict between proxies `$name`");
            }
        } else {
            parent::push($multiProxy = new $class($name, $proxy->isStatic()), $proxies);
        }

        $multiProxy->addProxy($proxy);
    }
}
