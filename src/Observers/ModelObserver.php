<?php
/**
 * Create an Observer to add a callback on a specific model event.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Observers;

use Laramore\Traits\IsLocked;
use Closure;

class ModelObserver extends BaseObserver
{
    /**
     * All possible events.
     *
     * @var array
     */
    protected static $events = [
        'retrieved', 'creating', 'created', 'updating', 'updated',
        'saving', 'saved', 'restoring', 'restored', 'replicating',
        'deleting', 'deleted', 'forceDeleted',
    ];

    /**
     * Return all possible events
     *
     * @return array
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * Actions during locking.
     *
     * @return void
     */
    protected function locking()
    {
        // Nothing to do here.
    }
}
