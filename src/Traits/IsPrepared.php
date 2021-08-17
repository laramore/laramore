<?php
/**
 * Add a prepare management for metas mainly.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Traits;

use Laramore\Exceptions\PrepareException;

trait IsPrepared
{
    /**
     * Indicate if the instance is preparing stuff or not.
     *
     * @var boolean
     */
    protected $preparing = false;

    /**
     * Indicate if the instance is prepared or not.
     *
     * @var boolean
     */
    protected $prepared = false;

    /**
     * Prepare the instance.
     *
     * @return self
     */
    public function setPreparing()
    {
        // Check if the instance is already prepared.
        $this->needsToBeUnpreparing();
        $this->needsToBeUnprepared();

        $this->preparing = true;

        // Custom preparing for each instance.
        $this->preparing();

        return $this;
    }

    /**
     * Set the instance as prepared.
     *
     * @return self
     */
    public function setPrepared()
    {
        // Check if the instance is already prepared.
        $this->needsToBePreparing();
        $this->needsToBeUnprepared();

        $this->prepared = true;

        // Custom preparing for each instance.
        $this->prepared();

        $this->preparing = false;

        return $this;
    }

    /**
     * Each class prepars in a specific way.
     *
     * @return void
     */
    abstract protected function preparing();

    /**
     * Each class prepars in a specific way.
     *
     * @return void
     */
    abstract protected function prepared();

    /**
     * Indicate if the instance is preparing stuff or not.
     *
     * @return boolean
     */
    public function isPreparing(): bool
    {
        return $this->preparing;
    }

    /**
     * Indicate if the instance is prepared or not.
     *
     * @return boolean
     */
    public function isPrepared(): bool
    {
        return $this->prepared;
    }

    /**
     * Return the preparing exception message.
     *
     * @param string $element Element requiring this instance not to be preparing.
     * @return string
     */
    protected function getPreparingMessage(string $element): string
    {
        return 'This instance `'.static::class."` is preparing and can not change/acces to the element `$element`.";
    }

    /**
     * Return the not preparing exception message.
     *
     * @param string $element Element requiring this instance to be preparing.
     * @return string
     */
    protected function getNotPreparingMessage(string $element): string
    {
        return 'This instance `'.static::class."` requires to be preparing to change/access to the element `$element`.";
    }

    /**
     * Return the prepared exception message.
     *
     * @param string $element Element requiring this instance not to be prepared.
     * @return string
     */
    protected function getPreparedMessage(string $element): string
    {
        return 'This instance `'.static::class."` is prepared and can not change/acces to the element `$element`.";
    }

    /**
     * Return the not prepared exception message.
     *
     * @param string $element Element requiring this instance to be prepared.
     * @return string
     */
    protected function getNotPreparedMessage(string $element): string
    {
        return 'This instance `'.static::class."` requires to be prepared to change/access to the element `$element`.";
    }

    /**
     * Throw an exception if the instance is the wrong prepare status.
     *
     * @param  boolean $preparing
     * @param  string  $preparingElement
     * @return self
     * @throws PrepareException If the instance is (un)preparing.
     */
    protected function checkNeedsToBePreparing(bool $preparing, string $preparingElement=null)
    {
        if ($this->isPreparing() != $preparing) {
            // Load the method calling the needsToBePreparing.
            if (\is_null($preparingElement)) {
                $preparingElement = $this->getDebugMethodName();
            }

            $message = $preparing ? 'getNotPreparingMessage' : 'getPreparingMessage';

            throw new PrepareException($this->$message($preparingElement), $preparingElement);
        }

        return $this;
    }

    /**
     * Throw an exception if the instance is unpreparing.
     *
     * @param string|null $preparingElement
     * @return self
     * @throws PrepareException If the instance is unpreparing.
     */
    public function needsToBePreparing(string $preparingElement=null)
    {
        return $this->checkNeedsToBePreparing(true, $preparingElement);
    }

    /**
     * Throw an exception if the instance is preparing.
     *
     * @param string|null $preparingElement
     * @return self
     * @throws PrepareException If the instance is preparing.
     */
    public function needsToBeUnpreparing(string $preparingElement=null)
    {
        return $this->checkNeedsToBePreparing(false, $preparingElement);
    }

    /**
     * Throw an exception if the instance is the wrong prepare status.
     *
     * @param  boolean $prepared
     * @param  string  $preparedElement
     * @return self
     * @throws PrepareException If the instance is (un)prepared.
     */
    protected function checkNeedsToBePrepared(bool $prepared, string $preparedElement=null)
    {
        if ($this->isPrepared() != $prepared) {
            // Load the method calling the needsToBePrepared.
            if (\is_null($preparedElement)) {
                $preparedElement = $this->getDebugMethodName();
            }

            $message = $prepared ? $this->getNotPreparedMessage($preparedElement) : $this->getPreparedMessage($preparedElement);

            throw new PrepareException($message, $preparedElement);
        }

        return $this;
    }

    /**
     * Throw an exception if the instance is unprepared.
     *
     * @param string|null $preparedElement
     * @return self
     * @throws PrepareException If the instance is unprepared.
     */
    public function needsToBePrepared(string $preparedElement=null)
    {
        return $this->checkNeedsToBePrepared(true, $preparedElement);
    }

    /**
     * Throw an exception if the instance is prepared.
     *
     * @param string|null $preparedElement
     * @return self
     * @throws PrepareException If the instance is prepared.
     */
    public function needsToBeUnprepared(string $preparedElement=null)
    {
        return $this->checkNeedsToBePrepared(false, $preparedElement);
    }
}
