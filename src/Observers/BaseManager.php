<?php
/**
 * Group all handlers in a manager.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Observers;

use Laramore\Traits\IsLocked;
use ReflectionClass;

abstract class BaseManager
{
    use IsLocked;

    /**
     * Allowed observable sub class.
     *
     * @var string
     */
    protected $managedClass;

    /**
     * The observable handler class to generate.
     *
     * @var string
     */
    protected $handlerClass;

    /**
     * List of all handlers.
     *
     * @var array
     */
    protected $handlers = [];

    /**
     * Observe all model events with our observers.
     *
     * @return void
     */
    protected function locking()
    {
        foreach ($this->handlers as $handler) {
            $handler->lock();
        }
    }

    /**
     * Indicate if the given class is observable by this manager.
     *
     * @param  string $observableClass
     * @return boolean
     */
    public function doesManage(string $observableClass): bool
    {
        return (new ReflectionClass($observableClass))->isSubclassOf($this->managedClass);
    }

    /**
     * Create an Handler for a specific observable class.
     *
     * @param  string $observableClass
     * @return BaseHandler
     */
    public function createHandler(string $observableClass): BaseHandler
    {
        $this->needsToBeUnlocked();

        if (!$this->doesManage($observableClass)) {
            throw new \Exception("The class [$observableClass] is not manageable by this type of handler");
        }

        if (in_array($observableClass, array_keys($this->handlers))) {
            throw new \Exception("A handler already exists for [$observableClass]");
        }

        $this->handlers[$observableClass] = $handler = new $this->handlerClass($observableClass);

        return $handler;
    }

    /**
     * Indicate if an observable handler is managed.
     *
     * @param  string $observableClass
     * @return boolean
     */
    public function hasHandler(string $observableClass): bool
    {
        return isset($this->handlers[$observableClass]);
    }

    /**
     * Return the observable handler for the given observable class.
     *
     * @param  string $observableClass
     * @return BaseHandler
     */
    public function getHandler(string $observableClass): BaseHandler
    {
        return $this->handlers[$observableClass];
    }

    /**
     * Return the list of the observable handlers.
     *
     * @return array
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }
}
