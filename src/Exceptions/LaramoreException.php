<?php
/**
 * Laramore exception class.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Exceptions;

class LaramoreException extends \RuntimeException
{
    /**
     * The instance creating this exception.
     *
     * @var object
     */
    protected $instance;

    /**
     * Create a new LaramoreException.
     *
     * @param object     $instance
     * @param string     $message
     * @param integer    $code
     * @param \Throwable $previous
     */
    public function __construct(object $instance, string $message, int $code=0, \Throwable $previous=null)
    {
        $this->instance = $instance;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Return the instance generating the exception.
     *
     * @return object
     */
    public function getInstance(): object
    {
        return $this->instance;
    }
}
