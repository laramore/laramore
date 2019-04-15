<?php

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

    public function isOwned()
    {
        return (bool) $this->getOwner();
    }

    public function lock()
    {
        $this->checkLock();

        if (!$this->isOwned()) {
            throw new \Exception('The field has no owner, cannot lock it');
        }

        $this->locked = true;

        return $this;
    }

    public function isLocked()
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
