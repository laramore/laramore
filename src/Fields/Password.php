<?php
/**
 * Define a email field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

use Illuminate\Support\Facades\Hash;

class Password extends Pattern
{
    protected $minLength = 8;

    public const REGEX_MIN_MAX_CARACTER = '(?=\S{$min,$max})';
    public const REGEX_AT_LEAST_ONE_LOWERCASE = '(?=\S*[a-z])';
    public const REGEX_AT_LEAST_ONE_UPPERCASE = '(?=\S*[A-Z])';
    public const REGEX_AT_LEAST_ONE_NUMBER = '(?=\S*[\d])';
    public const REGEX_AT_LEAST_ONE_SPECIAL = '(?=\S*[\W])';

    // Need one lowercase caracter at least.
    public const NEED_ONE_LOWERCASE = 131072;

    // Need one uppercase caracter at least.
    public const NEED_ONE_UPPERCASE = 262144;

    // Need one number caracter at least.
    public const NEED_ONE_NUMBER = 524288;

    // Need one special caracter at least.
    public const NEED_ONE_SPECIAL = 1048576;

    // The password length must be at least of the defined length.
    public const MIN_LENGTH = 2097152;

    // The password length must be at max of the defined length.
    public const MAX_LENGTH = 4194304;

    // Default rules
    public const DEFAULT_PASSWORD = (self::DEFAULT_TEXT | self::MATCH_PATTERN | self::NEED_ONE_LOWERCASE | self::NEED_ONE_UPPERCASE | self::NEED_ONE_NUMBER | self::MIN_LENGTH ^ self::VISIBLE);

    protected static $defaultRules = self::DEFAULT_PASSWORD;

    protected function locking()
    {
        parent::locking();

        if (!$this->hasRule(self::MATCH_PATTERN)) {
            throw new \Exception('A password has to follow a pattern');
        }

        if ($this->hasRule(self::FIX_IF_WRONG)) {
            throw new \Exception('A password is not fixable');
        }

        $this->setProperty('pattern', $this->getPattern());
    }

    public function getPattern()
    {
        return '/^\S*'.implode('', $this->getRegexRules()).'\S*$/';
    }

    protected function getRegexRules()
    {
        $rules = [];

        if ($this->hasRule(self::MIN_LENGTH) || $this->hasRule(self::MAX_LENGTH)) {
            $rules[] = str_replace('$min', $this->minLength ?: '',
                str_replace('$max', $this->maxLength ?: '', static::REGEX_MIN_MAX_CARACTER)
            );
        }

        if ($this->hasRule(self::NEED_ONE_LOWERCASE)) {
            $rules[] = static::REGEX_AT_LEAST_ONE_LOWERCASE;
        }

        if ($this->hasRule(self::NEED_ONE_UPPERCASE)) {
            $rules[] = static::REGEX_AT_LEAST_ONE_UPPERCASE;
        }

        if ($this->hasRule(self::NEED_ONE_NUMBER)) {
            $rules[] = static::REGEX_AT_LEAST_ONE_NUMBER;
        }

        if ($this->hasRule(self::NEED_ONE_SPECIAL)) {
            $rules[] = static::REGEX_AT_LEAST_ONE_SPECIAL;
        }

        return $rules;
    }

    public function setValue($model, $value)
    {
        $value = parent::setValue($model, $value);

        return Hash::make($value);
    }

    public function fixValue($model, $value)
    {
        throw new \Exception('A password is not fixable');
    }

    public function checkValue($model, $value, $password=null, $boolean=true)
    {
        return Hash::check($password, $value) === $boolean;
    }
}
