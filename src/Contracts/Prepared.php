<?php
/**
 * Interface for all preparable classes.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Contracts;

use Laramore\Exceptions\PrepareException;

interface Prepared
{
    /**
     * Prepare the instance.
     *
     * @return self
     */
    public function setPreparing();

    /**
     * Set the instance as prepared.
     *
     * @return self
     */
    public function setPrepared();

    /**
     * Indicate if the instance is prepared or not.
     *
     * @return boolean
     */
    public function isPrepared(): bool;

    /**
     * Throw an exception if the instance is unprepared.
     *
     * @param string|null $preparedElement
     * @return self
     * @throws PrepareException If the instance is unprepared.
     */
    public function needsToBePrepared(string $preparedElement=null);

    /**
     * Throw an exception if the instance is prepared.
     *
     * @param string|null $preparedElement
     * @return self
     * @throws PrepareException If the instance is prepared.
     */
    public function needsToBeUnprepared(string $preparedElement=null);
}
