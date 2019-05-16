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
    use IsLocked {
        IsLocked::lock as private lockFromIsLocked
    }

    protected $owner;
    protected $locked = false;

    public function own($owner, string $name)
    {
        if ($this->isOwned()) {
            throw new \Exception('An owner has already been set');
        }

        $this->owner = $owner;
        $this->name($name);

        $this->owning();

        return $this;
    }

    abstract protected function owning();

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
        if (!$this->isOwned()) {
            throw new \Exception('The field has no owner, cannot lock it');
        }

        return $this->lockFromIsLocked();
    }
}
