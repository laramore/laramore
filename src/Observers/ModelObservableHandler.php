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

class ModelObservableHandler extends BaseObservableHandler
{
    protected $observerClass = ModelObserver::class;

    /**
     * List of all possible events on models.
     *
     * @var array
     */
    protected $events = [
        'retrieved', 'creating', 'created', 'updating', 'updated',
        'saving', 'saved', 'restoring', 'restored', 'replicating',
        'deleting', 'deleted', 'forceDeleted',
    ];

    /**
     * Observe all model events with our observers.
     *
     * @return void
     */
    protected function locking()
    {
        foreach ($this->observers as $observer) {
            foreach ($observer->getObserved() as $event) {
                $this->observableClass::addModelEvent($event, $observer->getCallback());
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

            if (count($args) === 1 && $args[0] instanceof $this->observerClass) {
                $this->addObserver($args[0]->observe($method));
            } else {
                $this->createObserver($method, ...$args);
            }
        } else {
            throw new \Exception('The event does not exists');
        }

        return $this;
    }
}
