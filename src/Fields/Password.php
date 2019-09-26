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
use Illuminate\Support\Str;
use Laramore\Validations\Pattern as PatternValidation;

class Password extends Pattern
{
    protected $minLength = 8;

    public const REGEX_MIN_MAX_CARACTER = '(?=\S{$min,$max})';
    public const REGEX_AT_LEAST_ONE_LOWERCASE = '(?=\S*[a-z])';
    public const REGEX_AT_LEAST_ONE_UPPERCASE = '(?=\S*[A-Z])';
    public const REGEX_AT_LEAST_ONE_NUMBER = '(?=\S*[\d])';
    public const REGEX_AT_LEAST_ONE_SPECIAL = '(?=\S*[\W])';

    // Need one lowercase caracter at least.
    public const NEED_ONE_LOWERCASE = 32768;

    // Need one uppercase caracter at least.
    public const NEED_ONE_UPPERCASE = 65536;

    // Need one number caracter at least.
    public const NEED_ONE_NUMBER = 131072;

    // Need one special caracter at least.
    public const NEED_ONE_SPECIAL = 262144;

    // Default rules
    public const DEFAULT_PASSWORD = (self::NEED_ONE_LOWERCASE | self::NEED_ONE_UPPERCASE | self::NEED_ONE_NUMBER | self::DEFAULT_PATTERN ^ self::VISIBLE);

    protected static $defaultRules = self::DEFAULT_PASSWORD;

    protected function checkRules()
    {
        parent::checkRules();

        if (!$this->hasRule(self::MATCH_PATTERN)) {
            throw new \Exception('A password has to follow a pattern');
        }
    }

    protected function setValidations()
    {
        $this->setProperty('pattern', $this->generatePattern());

        parent::setValidations();

        $this->setValidation(PatternValidation::class)->type('password');
    }

    protected function setProxies()
    {
        parent::setProxies();

        $this->setProxy('hash', []);
        $this->setProxy('isCorrect', ['value'], ['model'], $this->generateProxyMethodName('is', 'correct'));
    }

    protected function generatePattern()
    {
        return '/^\S*'.implode('', $this->getRegexRules()).'\S*$/';
    }

    protected function getRegexRules()
    {
        $rules = [];

        if (!\is_null($this->minLength) || !\is_null($this->maxLength)) {
            $lengths = [$this->minLength ?: '', $this->maxLength ?: ''];
            $rules[] = str_replace(['$min', '$max'], $lengths, static::REGEX_MIN_MAX_CARACTER);
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

    public function transform($value)
    {
        return $this->hash(parent::transform($value));
    }

    public function hash($value)
    {
        return Hash::make($value);
    }

    public function isCorrect($value, $password=null, bool $expected=true)
    {
        return Hash::check($password, $value) === $expected;
    }
}
