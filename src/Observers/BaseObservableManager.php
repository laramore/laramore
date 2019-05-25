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

abstract class BaseObservableManager
{
    use IsLocked;

    protected $observableHandlers = [];
    protected $observableSubClass;
    protected $observableHandlerClass;

    /**
     * Observe all model events with our observers.
     *
     * @return void
     */
    protected function locking()
    {
        foreach ($this->observableHandlers as $observableHandler) {
            $observableHandler->lock();
        }
    }

    public function isObservable(string $observableClass)
    {
        return (new ReflectionClass($observableClass))->isSubclassOf($this->observableSubClass);
    }

    public function createObservableHandler(string $observableClass)
    {
        $this->checkLock();

        if (!$this->isObservable($observableClass)) {
            throw new \Exception('This class is not observable by this type of handler');
        }

        if (in_array($observableClass, array_keys($this->observableHandlers))) {
            throw new \Exception('A handler already exists for this observable');
        }

        $this->observableHandlers[$observableClass] = $handler = new $this->observableHandlerClass($observableClass);

        return $handler;
    }

    public function hasObservableHandler(string $observableClass)
    {
        return isset($this->observableHandlers[$observableClass]);
    }

    public function getObservableHandler(string $observableClass)
    {
        return $this->observableHandlers[$observableClass];
    }

    public function getObservableHandlers()
    {
        return $this->observableHandlers;
    }
}
