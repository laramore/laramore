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

abstract class BaseObserverHandler
{
    use IsLocked;

    protected $observers = [];

    /**
     * Add an observer for a specific model event.
     *
     * @param Observer $observer
     * @return self
     */
    public function addObserver(Observer $observer): self
    {
        $this->checkLock();

        $priority = $observer->getPriority();

        for ($i = (count($this->observers) - 1); $i >= 0; $i--) {
            if ($this->observers[$i]->getPriority() > $priority) {
                $this->observers = array_values(array_merge(
                    array_slice($this->observers, 0, $i),
                    [$observer],
                    array_slice($this->observers, $i),
                ));

                return $this;
            }
        }

        array_unshift($this->observers, $observer);

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
    public function createObserver($data, string $name, Closure $callback, int $priority=Observer::AVERAGE_PRIORITY)
    {
        return $this->addObserver(new Observer($name, $callback, $priority, $data));
    }

    public function hasObserver(string $name)
    {
        foreach ($this->observers as $key => $observer) {
            if ($observer->getName() === $name) {
                return true;
            }
        }

        return false;
    }

    public function getObserver(string $name)
    {
        foreach ($this->observers as $key => $observer) {
            if ($observer->getName() === $name) {
                return $this->observers;
            }
        }

        throw new \Exception('The observer does not exist');
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
}
