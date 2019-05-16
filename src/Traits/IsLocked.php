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

trait IsLocked
{
    protected $locked = false;

    public function lock()
    {
        $this->checkLock();

        $this->locking();

        $this->locked = true;

        return $this;
    }

    abstract protected function locking();

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function checkLock()
    {
        if ($this->isLocked()) {
            throw new \Exception('This is locked, nothing can change');
        }

        return $this;
    }
}
