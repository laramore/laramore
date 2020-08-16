<?php
/**
 * Create an Observer to add a callable on a specific model event.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Observers;

use Laramore\Traits\IsLocked;

abstract class BaseObserver
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
     * @var \Closure|callable|array|null
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
    protected $observed = [];

    /**
     * The top priority is the max one.
     * Define the limits.
     *
     * @var int
     */
    const MAX_PRIORITY = 0;
    const MEDIUM_PRIORITY = 50;
    const MIN_PRIORITY = 100;
    const HIGH_PRIORITY = ((self::MAX_PRIORITY + self::MEDIUM_PRIORITY) / 2);
    const LOW_PRIORITY = ((self::MIN_PRIORITY + self::MEDIUM_PRIORITY) / 2);

    /**
     * An observer needs at least a name and a callable.
     *
     * @param string                       $name
     * @param \Closure|callable|array|null $callback
     * @param integer                      $priority
     * @param mixed                        $data
     */
    public function __construct(string $name, $callback=null, int $priority=self::MEDIUM_PRIORITY, $data=[])
    {
        $this->setName($name);
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
     * Return the callable function.
     *
     * @return \Closure|callable|array
     */
    public function getCallback()
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
     * Define the name of the observer.
     *
     * @param string $name
     * @return self
     */
    public function setName(string $name)
    {
        $this->needsToBeUnlocked();

        $this->name = $name;

        return $this;
    }

    /**
     * Define the callable method until the observer is locked.
     *
     * @param \Closure|callable|array|null $callback
     * @return self
     */
    public function setCallback($callback=null)
    {
        $this->needsToBeUnlocked();

        if (!\is_callable($callback) && !is_null($callback)) {
            throw new \Exception('Expecting a valid callable object.');
        }

        $this->callback = $callback;

        return $this;
    }

    /**
     * Define the priority of this observer until it is locked.
     *
     * @param integer $priority
     * @return self
     */
    public function setPriority(int $priority)
    {
        $this->needsToBeUnlocked();

        if ($priority < static::MAX_PRIORITY || $priority > static::MIN_PRIORITY) {
            throw new \Exception('The priority must be beetween `'.static::MAX_PRIORITY.'` and `'.static::MIN_PRIORITY.'`');
        }

        $this->priority = $priority;

        return $this;
    }

    /**
     * Add one or more entities to observe.
     *
     * @param  string|array $entities
     * @return self
     */
    public function on($entities)
    {
        $this->needsToBeUnlocked();
        $entities = is_array($entities) ? $entities : [$entities];

        foreach ($entities as $element) {
            if (!in_array($element, $this->observed, true)) {
                $this->observed[] = $element;
            }
        }

        return $this;
    }

    /**
     * Observe only the given entities.
     *
     * @param  string|array $entities
     * @return self
     */
    public function only($entities)
    {
        $this->needsToBeUnlocked();

        $this->observed = [];

        return $this->on($entities);
    }

    /**
     * Do not observe one more entities.
     *
     * @param  string|array $entities
     * @return self
     */
    public function except($entities)
    {
        $this->needsToBeUnlocked();

        foreach ($this->observed as $key => $element) {
            if (!in_array($element, (array) $entities)) {
                unset($this->observed[$key]);
            }
        }

        return $this;
    }

    /**
     * Get all observed entities.
     *
     * @param string|array $entity
     * @return boolean
     */
    public function has($entity): bool
    {
        foreach ($this->all() as $observed) {
            if ($observed == $entity) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all observed entities.
     *
     * @param string|array $entity
     * @return array
     */
    public function get($entity)
    {
        foreach ($this->all() as $observed) {
            if ($observed == $entity) {
                return $observed;
            }
        }

        throw new \Exception("`$entity` not found !");
    }

    /**
     * Return the number of observed entities.
     *
     * @return integer
     */
    public function count(): int
    {
        return \count($this->observed);
    }

    /**
     * Get all observed entities.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->observed;
    }

    /**
     * Actions during locking.
     *
     * @return void
     */
    protected function locking()
    {
        if (!$this->getCallback()) {
            throw new \LogicException('An observer needs a callback value.');
        }
    }

    /**
     * Call the observer.
     *
     * @param  mixed ...$args
     * @return mixed
     */
    public function __invoke(...$args)
    {
        return \call_user_func($this->getCallback(), ...$args);
    }
}
