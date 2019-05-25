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

use Laramore\Traits\IsLocked;
use Laramore\Meta;

class ModelObserverManager
{
    use IsLocked;

    protected $modelObserverHandlers = [];

    /**
     * List of all possible events on models.
     *
     * @var array
     */
    protected $events = [];

    /**
     * A ModelObserver add all observers to handle model events.
     *
     * @param Meta $meta Meta of the model.
     */
    public function __construct(array $events)
    {
        $this->events = $events;
    }

    /**
     * Observe all model events with our observers.
     *
     * @return void
     */
    protected function locking()
    {
        foreach ((array) $this->modelObserverHandlers as $modelObserverHandler) {
            $modelObserverHandler->lock();
        }
    }

    public function createModelObserverHandler(Meta $meta)
    {
        $this->checkLock();

        if (in_array($modelClass = $meta->getModelClass(), array_keys($this->modelObserverHandlers))) {
            throw new \Excpetion('A handler already exists for this meta');
        }

        $this->modelObserverHandlers[$modelClass] = $handler = new ModelObserverHandler($meta, $this->events);

        return $handler;
    }

    public function hasModelObserverHandler(Meta $meta)
    {
        return isset($this->modelObserverHandlers[$meta->getModelClass()]);
    }

    public function getModelObserverHandler(Meta $meta)
    {
        return $this->modelObserverHandlers[$meta->getModelClass()];
    }

    public function getModelObserverHandlers()
    {
        return $this->modelObserverHandlers;
    }
}
