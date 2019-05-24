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

class Observer
{
    use IsLocked;

    /**
     * Each observer has a name, so it can be recognizable.
     *
     * @var string
     */
    protected $name;

    /**
     * Callback to trigger when a specific model event happens.
     *
     * @var Closure
     */
    protected $callback;

    /**
     * All observers are triggered for a specific model event, from the priority MAX_PRIORITY to MIN_PRIORITY.
     *
     * @var int
     */
    protected $priority;

    /**
     * All types to observe.
     *
     * @var array
     */

    protected $toObserve = [];

    /**
     * The top priority is the max one.
     * Define the limits.
     *
     * @var int
     */
    public const MAX_PRIORITY = 0;
    public const AVERAGE_PRIORITY = 50;
    public const MIN_PRIORITY = 100;
    public const HIGH_PRIORITY = ((self::MAX_PRIORITY + self::AVERAGE_PRIORITY) / 2);
    public const LOW_PRIORITY = ((self::MIN_PRIORITY + self::AVERAGE_PRIORITY) / 2);

    /**
     * An observer needs at least a name and a callback.
     *
     * @param string  $name
     * @param Closure $callback
     * @param integer $priority
     */
    public function __construct(string $name, Closure $callback, int $priority=self::AVERAGE_PRIORITY, $data=[])
    {
        $this->name = $name;

        $this->setCallback($callback);
        $this->setPriority($priority);
        $this->on($data);
    }

    /**
     * Return the name of the observer.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Return the callback function.
     *
     * @return Closure
     */
    public function getCallback(): Closure
    {
        return $this->callback;
    }

    /**
     * Return the priority of this observer.
     *
     * @return integer
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Define the callback method until the observer is locked.
     *
     * @param Closure $callback
     * @return static
     */
    public function setCallback(Closure $callback)
    {
        $this->checkLock();

        $this->callback = $callback;

        return $this;
    }

    /**
     * Define the priority of this observer until it is locked.
     *
     * @param integer $priority
     * @return static
     */
    public function setPriority(int $priority)
    {
        $this->checkLock();

        if ($priority < static::MAX_PRIORITY || $priority > static::MIN_PRIORITY) {
            throw new \Exception('The priority must be beetween '.static::MAX_PRIORITY.' and '.static::MIN_PRIORITY);
        }

        $this->priority = $priority;

        return $this;
    }

    public function on($data)
    {
        foreach ((array) $data as $element) {
            if (!in_array($element, $this->toObserve)) {
                $this->toObserve[] = $element;
            }
        }

        return $this;
    }

    public function onOnly($data)
    {
        $this->toObserve = [];

        return $this->for($data);
    }

    public function except($data)
    {
        foreach ($this->toObserve as $key => $element) {
            if (!in_array($element, (array) $data)) {
                unset($this->toObserve[$key]);
            }
        }

        return $this;
    }

    public function getAllToObserve(): array
    {
        return $this->toObserve;
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
