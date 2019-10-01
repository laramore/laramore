<?php
/**
 * Handle all observers for a specific model.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Laramore\Observers\BaseHandler;

class ModelEventHandler extends BaseHandler
{
    /**
     * The observable class.
     *
     * @var string
     */
    protected $observerClass = ModelEvent::class;

    /**
     * Observe all model events with our observers.
     *
     * @return void
     */
    protected function locking()
    {
        foreach ($this->observers as $observer) {
            foreach ($observer->all() as $event) {
                $this->observableClass::addModelEvent($event, $observer->getCallback());
            }
        }

        parent::locking();
    }
}
