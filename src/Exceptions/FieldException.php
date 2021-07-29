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

use Illuminate\Support\Arr;
use Laramore\Contracts\Field\Field;

class FieldException extends LaramoreException
{
    protected $field;

    protected $errors;

    public function __construct(Field $field, $errors, int $code=400)
    {
        $this->field = $field;
        $this->errors = Arr::wrap($errors);

        parent::__construct("The field {$field->getQualifiedName()} excepted: ".implode('. ', $this->errors), $code);
    }

    /**
     * Return the field that threw the exception.
     *
     * @return Field
     */
    public function field(): Field
    {
        return $this->field;
    }

    /**
     * Return the field that threw the exception.
     *
     * @return array
     */
    public function errors(): array
    {
        return [
            $this->field->getName() => $this->errors,
        ];
    }
}
