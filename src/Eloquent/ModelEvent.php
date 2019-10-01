<?php
/**
 * Create an Observer to add a \Closure on a specific model event.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Eloquent;

use Laramore\Traits\IsLocked;
use Laramore\Observers\BaseObserver;

class ModelEvent extends BaseObserver
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
}
