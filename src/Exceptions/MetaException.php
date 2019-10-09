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

use Laramore\Meta;

class MetaException extends LaramoreException
{
    /**
     * Create a new LaramoreException.
     *
     * @param Meta       $meta
     * @param string     $message
     * @param integer    $code
     * @param \Throwable $previous
     */
    public function __construct(Meta $meta, string $message, int $code=0, \Throwable $previous=null)
    {
        parent::__construct($meta, $message, $code, $previous);
    }

    public function getInstanceName()
    {
        $instance = $this->getInstance();

        return \get_class($instance).': '.$instance->getModelClass();
    }

    /**
     * Return the instance generating the exception.
     *
     * @return Meta
     */
    public function getMeta(): Meta
    {
        return $this->getInstance();
    }
}
