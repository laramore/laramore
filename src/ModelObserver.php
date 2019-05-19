<?php
/**
 * Handle all observers for a specific model.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore;

use Illuminate\Database\Eloquent\Model;
use Laramore\Traits\IsLocked;
use Closure;

class ModelObserver
{
    use IsLocked;

    protected $meta;
    protected $observed = false;
    protected $observers;

    /**
     * List of all possible events on models.
     *
     * @var array
     */
    protected static $events = [
        'retrieved', 'creating', 'created', 'updating', 'updated',
        'saving', 'saved', 'restoring', 'restored', 'replicating',
        'deleting', 'deleted', 'forceDeleted',
    ];

    /**
     * A ModelObserver add all observers to handle model events.
     *
     * @param Meta $meta Meta of the model.
     */
    public function __construct(Meta $meta)
    {
        $this->meta = $meta;
        $this->observers = array_fill_keys(static::$events, []);
    }

    /**
     * Observe all model events with our observers.
     *
     * @return void
     */
    protected function locking()
    {
        foreach (static::$events as $event) {
            foreach ((array) $this->observers[$event] as $observer) {
                $this->meta->getModelClass()::$event($observer->lock()->getCallback());
            }
        }
    }

    /**
     * Add an observer for a specific model event.
     *
     * @param string   $event
     * @param Observer $observer
     * @return static
     */
    public function addObserver(string $event, Observer $observer)
    {
        $this->checkLock();

        $observers = $this->observers[$event];
        $priority = $observer->getPriority();

        for ($i = (count($observers) - 1); $i >= 0; $i--) {
            if ($observers[$i]->getPriority() > $priority) {
                $this->observers[$event] = array_values(array_merge(
                    array_slice($observers, 0, $i),
                    [$observer],
                    array_slice($observers, $i),
                ));

                return $this;
            }
        }

        array_unshift($this->observers[$event], $observer);

        return $this;
    }

    /**
     * Create an observer and add it.
     *
     * @param  string  $event
     * @param  string  $name
     * @param  Closure $callback
     * @param  int  $priority
     * @return static
     */
    public function createObserver(string $event, string $name, Closure $callback, int $priority=Observer::AVERAGE_PRIORITY)
    {
        return $this->addObserver($event, new Observer($name, $callback, $priority));
    }

    /**
     * Remove an observer from a specific model event.
     *
     * @param  string $event
     * @param  string $name
     * @return static
     */
    public function removeObserver(string $event, string $name)
    {
        $this->checkLock();

        foreach ($this->observers[$event] as $key => $observer) {
            if ($observer->getName() === $name) {
                unset($this->observers[$event]);
            }
        }

        $this->observers[$event] = array_values($this->observers[$event]);

        return $this;
    }

    /**
     * Add or create an observer for a specific model event.
     *
     * @param  string $method
     * @param  array  $args
     * @return static        
     */
    public function __call(string $method, array $args)
    {
        if (in_array($method, static::$events)) {
            if ($this->observed) {
                throw new \Exception('Cannot add an observer. The model is already observed');
            }

            if (count($args) === 1 && $args[0] instanceof Observer) {
                $this->addObserver($method, $args[0]);
            } else {
                $this->createObserver($method, ...$args);
            }
        } else {
            throw new \Exception('The event does not exists');
        }

        return $this;
    }
}
