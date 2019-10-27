<?php
/**
 * Define a text field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

use Illuminate\Support\Facades\Schema;
use Laramore\Validations\NotBlank;
use Laramore\Elements\Type;
use Types;

class Text extends Field
{
    /**
     * Set of rules.
     * Common to all string fields.
     *
     * @var integer
     */

    // If the string is a blank value, throw an exception
    public const NOT_BLANK = 512;

    // Default rules
    public const DEFAULT_TEXT = (self::NOT_BLANK | self::DEFAULT_FIELD);

    protected static $defaultRules = self::DEFAULT_TEXT;

    public function getType(): Type
    {
        return Types::text();
    }

    protected function setValidations()
    {
        parent::setValidations();

        if ($this->hasRule(self::NOT_BLANK)) {
            $this->setValidation(NotBlank::class);
        }
    }

    public function dry($value)
    {
        return $this->transform($value);
    }

    public function cast($value)
    {
        return $this->transform($value);
    }

    public function transform($value)
    {
        return is_null($value) ? $value : (string) $value;
    }

    public function serialize($value)
    {
        return $value;
    }
}
