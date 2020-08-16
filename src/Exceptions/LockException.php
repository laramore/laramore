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
    /**
     * Element creating this exception.
     * Could be a method or a attribute.
     *
     * @var string
     */
    protected $element;

    /**
     * Create a new LaramoreException.
     *
     * @param string     $message
     * @param string     $element
     * @param integer    $code
     * @param \Throwable $previous
     */
    public function __construct(string $message, string $element, int $code=0, \Throwable $previous=null)
    {
        $this->element = $element;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Return the element generating the issue.
     *
     * @return string|null
     */
    public function getElement(): string
    {
        return $this->element;
    }
}
