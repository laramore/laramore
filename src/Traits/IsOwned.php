<?php
/**
 * Add an owner management.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Traits;

use Laramore\Exceptions\OwnException;

trait IsOwned
{
    /**
     * The instance owned this instance.
     *
     * @var mixed
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
        $class = static::class;

        return "The instance `$class` `{$this->name}` is already owned";
    }

    /**
     * Return the owned exception message.
     *
     * @return string
     */
    protected function getNotOwnedMessage(): string
    {
        $class = static::class;

        return "The instance `$class` `{$this->name}` needs to be owned";
    }

    /**
     * Set the owner.
     *
     * @param mixed $owner
     * @return void
     */
    protected function setOwner($owner)
    {
        $this->needsToBeUnowned();

        $this->owner = $owner;
    }

    /**
     * Assign a unique owner to this instance.
     *
     * @param  mixed  $owner
     * @param  string $name
     * @return self
     */
    public function ownedBy($owner, string $name)
    {
        $this->setOwner($owner);
        $this->setName($name);

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
     * @param  string $name
     * @return self
     */
    abstract protected function setName(string $name);

    /**
     * Return the owner of this instance.
     *
     * @return mixed
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Indicate if this instance is owned or not.
     *
     * @return boolean
     */
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
     * @throws OwnException If the instance is (un)owned.
     */
    protected function checkNeedsToBeOwned(bool $owned, string $ownedElement=null)
    {
        if ($this->isOwned() !== $owned) {
            // Load the method calling the needsToBeOwned.
            if (\is_null($ownedElement)) {
                $ownedElement = $this->getDebugMethodName();
            }

            throw new OwnException($owned ? $this->getNotOwnedMessage() : $this->getOwnedMessage(), $ownedElement);
        }

        return $this;
    }

    /**
     * Throw an exception if the instance is unowned.
     *
     * @param string|null $ownedElement
     * @return self
     * @throws OwnException If the instance is unowned.
     */
    public function needsToBeOwned(string $ownedElement=null)
    {
        return $this->checkNeedsToBeOwned(true, $ownedElement);
    }

    /**
     * Throw an exception if the instance is owned.
     *
     * @param string|null $ownedElement
     * @return self
     * @throws OwnException If the instance is owned.
     */
    public function needsToBeUnowned(string $ownedElement=null)
    {
        return $this->checkNeedsToBeOwned(false, $ownedElement);
    }
}
