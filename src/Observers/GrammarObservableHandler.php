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

class GrammarObservableHandler extends BaseObservableHandler
{
    /**
     * The observer class to use to generate.
     *
     * @var string
     */
    protected $observerClass = GrammarObserver::class;

    /**
     * Observe all model events with our observers.
     *
     * @return void
     */
    protected function locking()
    {
        foreach ($this->observers as $observer) {
            foreach ($observer->getObserved() as $type) {
                $this->observableClass::macro('type'.ucfirst($type), $observer->getCallback());
            }

            $observer->lock();
        }
    }
}
