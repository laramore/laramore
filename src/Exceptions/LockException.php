<?php
/**
 * This exception indicate that we tried to edit a locked instance.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Exceptions;

class LockException extends LaramoreException
{
    protected $element;

    /**
     * Create a new LaramoreException.
     *
     * @param object     $instance
     * @param string     $message
     * @param string     $element
     * @param integer    $code
     * @param \Throwable $previous
     */
    public function __construct(object $instance, string $message, string $element, int $code=0, \Throwable $previous=null)
    {
        $this->element = $element;

        parent::__construct($instance, $message, $code, $previous);
    }

    /**
     * Return the locked element generating the issue.
     *
     * @return string|null
     */
    public function getElement(): string
    {
        return $this->element;
    }
}
