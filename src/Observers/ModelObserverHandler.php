<?php
/**
 * Handle all observers for a specific model.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Observers;

use Illuminate\Database\Eloquent\Model;
use Laramore\Observers\BaseObserverHandler;
use Laramore\Meta;
use Closure;

class ModelObserverHandler extends BaseObserverHandler
{
    /**
     * Meta for this observer.
     *
     * @var Meta
     */
    protected $meta;

    /**
     * List of all possible events on models.
     *
     * @var array
     */
    protected $events = [];

    /**
     * A ModelObserver add all observers to handle model events.
     *
     * @param Meta  $meta   Meta of the model.
     * @param array $events
     */
    public function __construct(Meta $meta, array $events)
    {
        $this->meta = $meta;
        $this->events = $events;
    }

    /**
     * Observe all model events with our observers.
     *
     * @return void
     */
    protected function locking()
    {
        foreach ((array) $this->observers as $observer) {
            foreach (array_intersect($this->events, $observer->getAllToObserve()) as $event) {
                $this->meta->getModelClass()::$event($observer->getCallback());
            }

            $observer->lock();
        }
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
        if (in_array($method, $this->events)) {
            $this->checkLock();

            if (count($args) === 1 && $args[0] instanceof Observer) {
                $this->addObserver($args[0]->on($method));
            } else {
                $this->createObserver($method, ...$args);
            }
        } else {
            throw new \Exception('The event does not exists');
        }

        return $this;
    }
}
