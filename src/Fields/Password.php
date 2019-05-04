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
    public const REGEX_MIN_MAX_CARACTER = '(?=\S{$min,$max})';
    public const REGEX_AT_LEAST_ONE_LOWERCASE = '(?=\S*[a-z])';
    public const REGEX_AT_LEAST_ONE_UPPERCASE = '(?=\S*[A-Z])';
    public const REGEX_AT_LEAST_ONE_NUMBER = '(?=\S*[\d])';
    public const REGEX_AT_LEAST_ONE_SPECIAL = '(?=\S*[\W])';

    // Default rules
    public const DEFAULT_PASSWORD = (self::DEFAULT_TEXT | self::MATCH_PATTERN ^ self::VISIBLE);

    protected static $defaultRules = self::DEFAULT_PASSWORD;

    /**
     * Set of rules.
     * Common to all email fields.
     *
     * @var integer
     */
    public function getDefaultProperties(): array
    {
        return array_merge(parent::getDefaultProperties(), [
            'minLength' => 8,
            'lowercase' => true,
            'uppercase' => true,
            'number' => true,
        ]);
    }

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

        if ($this->minLength) {
            $rules[] = str_replace('$min', $this->minLength,
                str_replace('$max', $this->length ?: '', static::REGEX_MIN_MAX_CARACTER)
            );
        }

        if ($this->lowercase) {
            $rules[] = static::REGEX_AT_LEAST_ONE_LOWERCASE;
        }

        if ($this->uppercase) {
            $rules[] = static::REGEX_AT_LEAST_ONE_UPPERCASE;
        }

        if ($this->number) {
            $rules[] = static::REGEX_AT_LEAST_ONE_NUMBER;
        }

        if ($this->special) {
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
