<?php
/**
 * This exception indicate that an issue was detected in field management.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2021
 * @license MIT
 */

namespace Laramore\Exceptions;

use Laramore\Contracts\Field\Field;

class FieldException extends LaramoreException
{
    protected $field;

    public function __construct(Field $field, string $message, int $code=400)
    {
        $this->field = $field;

        parent::__construct($message, $code);
    }

    /**
     * Return the field that threw the exception.
     *
     * @return Field
     */
    public function getField(): Field
    {
        return $this->field;
    }
}
