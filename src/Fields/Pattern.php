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

class Pattern extends Text
{
    protected $type = 'string';
    protected $pattern = '//';

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
    public const DEFAULT_PATTERN = (self::NOT_NULLABLE | self::VISIBLE | self::FILLABLE | self::FIX_IF_WRONG);

    protected function __construct($rules='DEFAULT_PATTERN', $default=null)
    {
        parent::__construct($rules, $default);
    }

    public function pattern(string $pattern)
    {
        $this->checkLock();

        $this->pattern = $pattern;

        return $this;
    }

    public function setValue($model, $value)
    {
        $value = parent::setValue($model, $value);

        if (is_null($value)) {
            return $value;
        }

        if (!preg_match($this->pattern, $value)) {
            if ($this->hasRule(self::MATCH_PATTERN, self::STRICT)) {
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
