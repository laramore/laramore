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

class MultipleExceptionsException extends LaramoreException
{
    /**
     * The instance creating this exception.
     *
     * @var object
     */
    protected $exceptions;

    /**
     * Create a new LaramoreException.
     *
     * @param object     $instance
     * @param array      $exceptions
     * @param integer    $code
     * @param \Throwable $previous
     */
    public function __construct(object $instance, array $exceptions, int $code=0, \Throwable $previous=null)
    {
        $this->exceptions = $exceptions;

        parent::__construct($instance, implode('. ', array_map(function ($exception) {
            return $exception->getMessage();
        }, $this->getExceptions())), $code, $previous);
    }

    public function getExceptions()
    {
        return $this->exceptions;
    }

    public function getFirstException()
    {
        return reset($this->exceptions);
    }

    /**
     * Return the instance generating the exception.
     *
     * @return object
     */
    public function __call(string $method, array $args)
    {
        return $this->getFirstException()->$method(...$args);
    }

    /**
     * Return the instance generating the exception.
     *
     * @return object
     */
    public function __get(string $key)
    {
        return $this->getFirstException()->$key;
    }
}
