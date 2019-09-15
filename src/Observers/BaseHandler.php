<?php
/**
 * Handle all observers for a specific class.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Observers;

use Laramore\Traits\IsLocked;

abstract class BaseHandler
{
    use IsLocked;

    /**
     * The observable class.
     *
     * @var string
     */
    protected $observableClass;

    /**
     * The observer class to use to generate.
     *
     * @var string
     */
    protected $observerClass;

    /**
     * List of all observers to apply on the observable class.
     *
     * @var array
     */
    protected $observers = [];

    /**
     * Create an Handler for a specific class.
     *
     * @param string $observableClass
     */
    public function __construct(string $observableClass)
    {
        $this->observableClass = $observableClass;
    }

    /**
     * Get the current observable class.
     *
     * @return string
     */
    public function getObservableClass(): string
    {
        return $this->observableClass;
    }

    /**
     * Get the current observable class.
     *
     * @return string
     */
    public function getObserverClass(): string
    {
        return $this->observerClass;
    }

    /**
     * Add an observer for a specific model event.
     *
     * @param BaseObserver $observer
     * @return self
     */
    public function add(BaseObserver $observer)
    {
        $this->needsToBeUnlocked();

        $observerClass = $this->getObserverClass();

        if (!($observer instanceof $observerClass)) {
            throw new \Exception("The observer [$observerClass] is not of the right type");
        }

        return $this->push($observer, $this->observers);
    }

    /**
     * Add an observer to a list of observers.
     *
     * @param BaseObserver $observer
     * @param array        $observers
     * @return self
     */
    protected function push(BaseObserver $observer, array &$observers)
    {
        $priority = $observer->getPriority();

        for ($i = 0; $i < count($observers); $i++) {
            if ($observers[$i]->getPriority() > $priority) {
                $observers = array_values(array_merge(
                    array_slice($observers, 0, $i),
                    [$observer],
                    array_slice($observers, $i),
                ));

                return $this;
            }
        }

        array_push($observers, $observer);

        return $this;
    }

    /**
     * Create an observer and add it.
     *
     * @param  string|array $data
     * @param  string       $name
     * @param  \Closure     $callback
     * @param  integer      $priority
     * @return static
     */
    public function create($data, string $name, \Closure $callback, int $priority=BaseObserver::MEDIUM_PRIORITY)
    {
        return $this->add(new $this->observerClass($name, $callback, $priority, $data));
    }

    /**
     * Return if an observe exists with the given name.
     *
     * @param  string $name
     * @return boolean
     */
    public function has(string $name): bool
    {
        foreach ($this->observers as $key => $observer) {
            if ($observer->getName() === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return the first observer with the given name.
     *
     * @param  string $name
     * @return BaseObserver
     */
    public function get(string $name)
    {
        foreach ($this->observers as $observer) {
            if ($observer->getName() === $name) {
                return $observer;
            }
        }

        throw new \Exception("The observer [$name] does not exist");
    }

    /**
     * Return the list of the handled observers.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->observers;
    }

    /**
     * Remove an observer before it is locked.
     *
     * @param  string $name
     * @return static
     */
    public function remove(string $name)
    {
        $this->needsToBeUnlocked();

        foreach ($this->observers as $observer) {
            if ($observer->getName() === $name) {
                unset($this->observers);
            }
        }

        $this->observers = array_values($this->observers);

        return $this;
    }

    /**
     * Need to do anything.
     *
     * @return void
     */
    protected function locking()
    {
        foreach ($this->observers as $observer) {
            if (!$observer->isLocked()) {
                $observer->lock();
            }
        }
    }
}
