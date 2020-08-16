<?php
/**
 * Interface for all ownable classes.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Contracts;

use Laramore\Exceptions\OwnException;

interface Owned
{
    /**
     * Assign a unique owner to this instance.
     *
     * @param  mixed  $owner
     * @param  string $name
     * @return self
     */
    public function own($owner, string $name);

    /**
     * Return the owner of this instance.
     *
     * @return mixed
     */
    public function getOwner();

    /**
     * Indicate if this instance is owned or not.
     *
     * @return boolean
     */
    public function isOwned(): bool;

    /**
     * Throw an exception if the instance is unowned.
     *
     * @param string|null $ownedElement
     * @return self
     * @throws OwnException If the instance is unowned.
     */
    public function needsToBeOwned(string $ownedElement=null);

    /**
     * Throw an exception if the instance is owned.
     *
     * @param string|null $ownedElement
     * @return self
     * @throws OwnException If the instance is owned.
     */
    public function needsToBeUnowned(string $ownedElement=null);
}
