<?php
/**
 * Add an owner and a lock management.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Traits;

trait IsOwnedAndLocked
{
    protected $owner;
    protected $locked = false;

    public function own($owner, string $name)
    {
        if ($this->isOwned()) {
            throw new \Exception('An owner has already been set');
        }

        $this->name = $name;
        $this->owner = $owner;

        return $this;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function isOwned(): bool
    {
        return (bool) $this->getOwner();
    }

    public function lock()
    {
        $this->checkLock();

        if (!$this->isOwned()) {
            throw new \Exception('The field has no owner, cannot lock it');
        }

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
            throw new \Exception('The field is locked, nothing can change');
        }

        return $this;
    }
}
