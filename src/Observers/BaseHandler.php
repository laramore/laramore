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
            throw new \Exception("The observer `$observerClass` is not of the right type");
        }

        return $this->push($observer, $this->observers);
    }

    /**
     * Add an observer to a list of observers.
     *
     * @param BaseObserver        $observer
     * @param array<BaseObserver> $observers
     * @return self
     */
    protected function push(BaseObserver $observer, array &$observers)
    {
        $priority = $observer->getPriority();

        for ($i = 0; $i < \count($observers); $i++) {
            if ($observers[$i]->getPriority() > $priority) {
                $observers = \array_values(\array_merge(
                    \array_slice($observers, 0, $i),
                    [$observer],
                    \array_slice($observers, $i),
                ));

                return $this;
            }
        }

        \array_push($observers, $observer);

        return $this;
    }

    /**
     * Create an observer and add it.
     *
     * @param  string|array            $data
     * @param  string                  $name
     * @param  \Closure|callable|array $callback
     * @param  integer                 $priority
     * @param  string                  $class
     * @return self
     */
    public function create($data, string $name, $callback,
                           int $priority=BaseObserver::MEDIUM_PRIORITY, string $class=null)
    {
        if (!\is_null($class)) {
            $subClass = $this->getObserverClass();

            if (!($class instanceof $subClass)) {
                throw new \LogicException("The class `$class` must an instance of the observer class `$subClass`");
            }
        } else {
            $class = $this->getObserverClass();
        }

        $this->add($observer = new $class($name, $callback, $priority, $data));

        return $observer;
    }

    /**
     * Return if an observe exists with the given name.
     *
     * @param  string $name
     * @param  string $class
     * @return boolean
     */
    public function has(string $name, string $class=null): bool
    {
        foreach ($this->observers as $observer) {
            if ($observer->getName() === $name) {
                if (\is_null($class) || ($observer instanceof $class)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Return the first observer with the given name.
     *
     * @param  string $name
     * @param  string $class
     * @return BaseObserver
     */
    public function get(string $name, string $class=null)
    {
        foreach ($this->observers as $observer) {
            if ($observer->getName() === $name) {
                if (\is_null($class) || ($observer instanceof $class)) {
                    return $observer;
                }
            }
        }

        throw new \Exception("The observer `$name` does not exist");
    }

    /**
     * Return the number of the handled observers.
     *
     * @param  string $class
     * @return integer
     */
    public function count(string $class=null): int
    {
        return \count($this->all($class));
    }

    /**
     * Return the list of the handled observers.
     *
     * @param  string $class
     * @return array<BaseObserver>
     */
    public function all(string $class=null): array
    {
        if (\is_null($class)) {
            return $this->observers;
        }

        return \array_filter($this->observers, function ($observer) use ($class) {
            return $observer instanceof $class;
        });
    }

    /**
     * Remove an observer before it is locked.
     *
     * @param  string $name
     * @param  string $class
     * @return self
     */
    public function remove(string $name, string $class=null)
    {
        $this->needsToBeUnlocked();

        foreach ($this->observers as $key => $observer) {
            if ($observer->getName() === $name) {
                if (\is_null($class) || ($observer instanceof $class)) {
                    unset($this->observers[$key]);

                    $this->observers = \array_values($this->all());

                    break;
                }
            }
        }

        return $this;
    }

    /**
     * Need to lock every observer.
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
