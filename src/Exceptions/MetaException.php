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
     * Meta were occured the error.
     *
     * @var Meta
     */
    protected $meta;

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
        parent::__construct($message, $code, $previous);
    }

    /**
     * Return the instance generating the exception.
     *
     * @return Meta
     */
    public function getMeta(): Meta
    {
        return $this->meta;
    }
}
