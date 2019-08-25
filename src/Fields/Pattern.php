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

use Laramore\Validations\Pattern as PatternValidation;

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

    // Default rules
    public const DEFAULT_PATTERN = (self::MATCH_PATTERN | self::DEFAULT_TEXT);

    protected static $defaultRules = self::DEFAULT_PATTERN;

    protected function setValidations()
    {
        parent::setValidations();

        if ($this->hasRule(self::MATCH_PATTERN)) {
            $this->setValidation(PatternValidation::class)->pattern($this->pattern);
        }
    }
}
