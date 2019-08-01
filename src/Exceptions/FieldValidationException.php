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

use Laramore\Fields\BaseField;
use Laramore\Validations\ValidationErrorBag;

class FieldValidationException extends ValidationException
{
    protected $errors;

    public function __construct(BaseField $field, ValidationErrorBag $errors, int $code=0, \Throwable $previous=null)
    {
        $this->errors = $errors;

        parent::__construct($field, implode(' ', $errors->all()), $code, $previous);
    }

    public function getField()
    {
        return $this->getInstance();
    }

    public function getErrors(): ValidationErrorBag
    {
        return $this->errors;
    }
}
