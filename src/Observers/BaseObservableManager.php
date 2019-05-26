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

    /**
     * Allowed observable sub class.
     *
     * @var string
     */
    protected $observableSubClass;

    /**
     * The observable handler class to generate.
     *
     * @var string
     */
    protected $observableHandlerClass;

    /**
     * List of all handlers.
     *
     * @var array
     */
    protected $observableHandlers = [];

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

    /**
     * Indicate if the given class is observable by this manager.
     *
     * @param  string  $observableClass
     * @return boolean
     */
    public function isObservable(string $observableClass): bool
    {
        return (new ReflectionClass($observableClass))->isSubclassOf($this->observableSubClass);
    }

    /**
     * Create an ObservableHandler for a specific observable class.
     *
     * @param  string $observableClass
     * @return BaseObservableHandler
     */
    public function createObservableHandler(string $observableClass): BaseObservableHandler
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

    /**
     * Indicate if an observable handler is managed.
     *
     * @param  string  $observableClass
     * @return boolean
     */
    public function hasObservableHandler(string $observableClass): bool
    {
        return isset($this->observableHandlers[$observableClass]);
    }

    /**
     * Return the observable handler for the given observable class.
     *
     * @param  string                $observableClass
     * @return BaseObservableHandler
     */
    public function getObservableHandler(string $observableClass): BaseObservableHandler
    {
        return $this->observableHandlers[$observableClass];
    }

    /**
     * Return the list of the observable handlers.
     *
     * @return array
     */
    public function getObservableHandlers(): array
    {
        return $this->observableHandlers;
    }
}
