<?php
/**
 * Define a pattern field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

abstract class Pattern extends Char
{
    protected $pattern;

    /**
     * Set of rules.
     * Common to all pattern fields.
     *
     * @var integer
     */
    // Except if the value does not match the pattern
    public const MATCH_PATTERN = 32768;

    // Fix if wrong
    public const FIX_IF_WRONG = 65536;

    // Default rules
    public const DEFAULT_PATTERN = (self::FIX_IF_WRONG | self::DEFAULT_TEXT);

    protected static $defaultRules = self::DEFAULT_PATTERN;

    public function getPattern()
    {
        return $this->pattern;
    }

    public function setValue($model, $value)
    {
        $value = parent::setValue($model, $value);

        if (is_null($value)) {
            return $value;
        }

        if (!preg_match($this->getPattern(), $value)) {
            if ($this->hasRule(self::MATCH_PATTERN)) {
                throw new \Exception('The value does not match the pattern of the field `'.$this->name.'`');
            }

            if ($this->hasRule(self::FIX_IF_WRONG)) {
                return $this->fixValue($model, $value);
            }
        }

        return $value;
    }

    public function fixValue($model, $value)
    {
        return $this->default;
    }
}
