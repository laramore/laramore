<?php
/**
 * Add a lock management.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Traits;

use Laramore\Exceptions\LockException;

trait IsLocked
{
    protected $locked = false;

    /**
     * Disallow any modifications after locking the instance.
     *
     * @return self
     */
    public function lock(): self
    {
        // Check if the instance is already locked.
        $this->checkLock();

        // Custom locking for each instance.
        $this->locking();

        $this->locked = true;

        return $this;
    }

    /**
     * Each class locks its instances in a specific way.
     *
     * @return void
     */
    abstract protected function locking();

    /**
     * Indicate if the instance is locked or not.
     *
     * @return boolean
     */
    public function isLocked(): bool
    {
        return $this->locked;
    }

    /**
     * Return the locked exception message.
     *
     * @return string
     */
    protected function getLockedMessage(): string
    {
        return 'An instance is locked and can not change';
    }

    /**
     * Throw an exception if the instance is locked.
     *
     * @param string|null $lockedElement
     * @return self
     */
    public function checkLock(string $lockedElement=null): self
    {
        if (!$this->isLocked()) {
            // Load the method calling the checkLock.
            if (\is_null($lockedElement)) {
                $lockedElement = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'].'()';
            }

            throw new LockException($this, $this->getLockedMessage(), $lockedElement);
        }

        return $this;
    }
}
