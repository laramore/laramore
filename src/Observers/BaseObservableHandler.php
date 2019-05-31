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

use Illuminate\Database\Eloquent\Model;
use Laramore\Traits\IsLocked;
use Closure;

abstract class BaseObservableHandler
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
     * Create an ObservableHandler for a specific class.
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
        return $this->obserableClass;
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
    public function addObserver(BaseObserver $observer): self
    {
        $this->checkLock();

        $observerClass = $this->getObserverClass();

        if (!($observer instanceof $observerClass)) {
            throw new \Exception('The observer is not of the right type');
        }

        $priority = $observer->getPriority();

        for ($i = 0; $i < count($this->observers); $i++) {
            if ($this->observers[$i]->getPriority() > $priority) {
                $this->observers = array_values(array_merge(
                    array_slice($this->observers, 0, $i),
                    [$observer],
                    array_slice($this->observers, $i),
                ));

                return $this;
            }
        }

        array_push($this->observers, $observer);

        return $this;
    }

    /**
     * Create an observer and add it.
     *
     * @param  string|array $data
     * @param  string       $name
     * @param  Closure      $callback
     * @param  integer      $priority
     * @return static
     */
    public function createObserver($data, string $name, Closure $callback, int $priority=BaseObserver::MEDIUM_PRIORITY)
    {
        return $this->addObserver(new $this->observerClass($name, $callback, $priority, $data));
    }

    /**
     * Return if an observe exists with the given name.
     *
     * @param  string $name
     * @return boolean
     */
    public function hasObserver(string $name): bool
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
    public function getObserver(string $name): BaseObserver
    {
        foreach ($this->observers as $key => $observer) {
            if ($observer->getName() === $name) {
                return $observer;
            }
        }

        throw new \Exception('The observer does not exist');
    }

    /**
     * Return the list of the handled observers.
     *
     * @return array
     */
    public function getObservers(): array
    {
        return $this->observers;
    }

    /**
     * Remove an observer before it is locked.
     *
     * @param  string $name
     * @return static
     */
    public function removeObserver(string $name)
    {
        $this->checkLock();

        foreach ($this->observers as $key => $observer) {
            if ($observer->getName() === $name) {
                unset($this->observers);
            }
        }

        $this->observers = array_values($this->observers);

        return $this;
    }

    /**
     * Add or create an observer for a specific method.
     *
     * @param  string $method
     * @param  array  $args
     * @return static
     */
    public function __call(string $method, array $args)
    {
        $this->checkLock();

        if (count($args) === 1 && $args[0] instanceof $this->observerClass) {
            $this->addObserver($args[0]->observe($method));
        } else {
            $this->createObserver($method, ...$args);
        }

        return $this;
    }
}
