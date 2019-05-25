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

class ModelObservableHandler extends BaseObservableHandler
{
    protected $observerClass = ModelObserver::class;

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
}
