<?php
/**
 * Observe all model events.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore;

use Laramore\Traits\IsLocked;

class Observer
{
    use IsLocked;

    protected $name;
    protected $callback;
    protected $priority;

    public const MAX_PRIORITY = 0;
    public const AVERAGE_PRIORITY = 50;
    public const MIN_PRIORITY = 100;

    public function __construct(string $name, \Closure $callback, int $priority)
    {
        $this->name = $name;

        $this->setCallback($callback);
        $this->setPriority($priority);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getCallback()
    {
        return $this->callback;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function setCallback(\Closure $callback)
    {
        $this->checkLock();

        $this->callback = $callback;
    }

    public function setPriority(int $priority)
    {
        $this->checkLock();

        if ($priority < static::MAX_PRIORITY || $priority > static::MIN_PRIORITY) {
            throw new \Exception('The priority must be beetween '.static::MAX_PRIORITY.' and '.static::MIN_PRIORITY);
        }

        $this->priority = $priority;
    }

    protected function locking()
    {
    }
}
