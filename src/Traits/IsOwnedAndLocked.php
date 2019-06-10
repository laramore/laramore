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

use Laramore\Exceptions\OwnException;

trait IsOwnedAndLocked
{
    use IsLocked {
        IsLocked::lock as private lockFromIsLocked;
    }

    /**
     * The instance owned this instance.
     *
     * @var object
     */
    protected $owner;

    /**
     * The defined name for this instance by its owner.
     *
     * @var string
     */
    protected $name;

    /**
     * Return the owned exception message.
     *
     * @return string
     */
    protected function getOwnedMessage(): string
    {
        return "The instance {$this->name} is already owned";
    }

    /**
     * Return the owned exception message.
     *
     * @return string
     */
    protected function getUnownedMessage(): string
    {
        return "The instance {$this->name} needs to be owned";
    }

    /**
     * Assign a unique owner to this instance.
     *
     * @param  object $owner
     * @param  string $name
     * @return self
     */
    public function own(object $owner, string $name): self
    {
        if ($this->isOwned()) {
            throw new \Exception('An owner has already been set');
        }

        $this->owner = $owner;
        $this->name($name);

        $this->owned();

        return $this;
    }

    /**
     * Callaback when the instance is owned.
     *
     * @return void
     */
    abstract protected function owned();

    /**
     * Define the name attribute.
     *
     * @return self
     */
    abstract public function name(string $name): self;

    /**
     * Return the owner of this instance.
     *
     * @return object|null
     */
    public function getOwner(): ?object
    {
        return $this->owner;
    }

    public function isOwned(): bool
    {
        return (bool) $this->getOwner();
    }

    /**
     * Throw an exception if the instance is the wrong own status.
     *
     * @param  boolean $owned
     * @param  string  $ownedElement
     * @return self
     */
    protected function checkNeedsToBeOwned(bool $owned, string $ownedElement=null): self
    {
        if ($this->isLocked() !== $owned) {
            // Load the method calling the needsToBeOwned.
            if (\is_null($ownedElement)) {
                $ownedElement = $this->getDebugMethodName();
            }

            throw new OwnException($this, $owned ? $this->getNotOwnedMessage() : $this->getOwnedMessage(), $ownedElement);
        }

        return $thios;
    }

    /**
     * Throw an exception if the instance is unowned.
     *
     * @param string|null $ownedElement
     * @return self
     */
    public function needsToBeOwned(string $ownedElement=null): self
    {
        return $this->checkNeedsToBeOwned(true, $ownedElement);
    }

    /**
     * Throw an exception if the instance is owned.
     *
     * @param string|null $ownedElement
     * @return self
     */
    public function needsToBeUnowned(string $ownedElement=null): self
    {
        return $this->checkNeedsToBeOwned(false, $ownedElement);
    }
}
