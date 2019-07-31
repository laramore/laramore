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
    /**
     * Indicate if the instance is locked or not.
     *
     * @var boolean
     */
    protected $locked = false;

    /**
     * Disallow any modifications after locking the instance.
     *
     * @return self
     */
    public function lock()
    {
        // Check if the instance is already locked.
        $this->needsToBeUnlocked();

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
        return 'This instance is locked and can not change';
    }

    /**
     * Return the not locked exception message.
     *
     * @return string
     */
    protected function getNotLockedMessage(): string
    {
        return 'This instance requires to be locked';
    }

    /**
     * Return the method name calling the need method.
     *
     * @return string
     */
    protected function getDebugMethodName(): string
    {
        return \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 4)[3]['function'].'()';
    }

    /**
     * Throw an exception if the instance is the wrong lock status.
     *
     * @param  boolean $locked
     * @param  string  $lockedElement
     * @return self
     */
    protected function checkNeedsToBeLocked(bool $locked, string $lockedElement=null)
    {
        if ($this->isLocked() !== $locked) {
            // Load the method calling the needsToBeLocked.
            if (\is_null($lockedElement)) {
                $lockedElement = $this->getDebugMethodName();
            }

            throw new LockException($this, $locked ? $this->getNotLockedMessage() : $this->getLockedMessage(), $lockedElement);
        }

        return $this;
    }

    /**
     * Throw an exception if the instance is unlocked.
     *
     * @param string|null $lockedElement
     * @return self
     */
    public function needsToBeLocked(string $lockedElement=null)
    {
        return $this->checkNeedsToBeLocked(true, $lockedElement);
    }

    /**
     * Throw an exception if the instance is locked.
     *
     * @param string|null $lockedElement
     * @return self
     */
    public function needsToBeUnlocked(string $lockedElement=null)
    {
        return $this->checkNeedsToBeLocked(false, $lockedElement);
    }
}
