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
use Laramore\Facades\Option;
use Laramore\Contracts\Field\PatternField;

class Password extends Char implements PatternField
{
    /**
     * Min length for a password.
     *
     * @var int
     */
    protected $minLength;

    /**
     * All patterns defined for this field.
     *
     * @var array
     */
    protected $patterns;

    /**
     * Return the pattern to match.
     *
     * @return string
     */
    public function getPattern(): string
    {
        return '/^\S*'.implode('', $this->getRegexOptions()).'\S*$/';
    }

    /**
     * Return all pattern flags
     *
     * @return mixed
     */
    public function getPatternFlags()
    {
        return null;
    }

    /**
     * Generate the regex options.
     *
     * @return array
     */
    protected function getRegexOptions(): array
    {
        $options = [];

        if (!\is_null($this->minLength) || !\is_null($this->maxLength)) {
            $lengths = [$this->minLength ?: '', $this->maxLength ?: ''];
            $options[] = str_replace(['$min', '$max'], $lengths, $this->patterns['min_max_part']);
        }

        if ($this->hasOption(Option::needLowercase())) {
            $options[] = $this->patterns['one_lowercase_part'];
        }

        if ($this->hasOption(Option::needUppercase())) {
            $options[] = $this->patterns['one_uppercase_part'];
        }

        if ($this->hasOption(Option::needNumber())) {
            $options[] = $this->patterns['one_number_part'];
        }

        if ($this->hasOption(Option::needSpecial())) {
            $options[] = $this->patterns['one_special_part'];
        }

        return $options;
    }

    /**
     * Cast the value to correspond to the field desire.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function cast($value)
    {
        // Do not cast with max length for passwords !
        $maxLength = $this->maxLength;
        $this->maxLength = null;

        $value = parent::cast($value);
        $this->maxLength = $maxLength;

        if (\is_null($value) || !Hash::needsRehash($value)) {
            return $value;
        }

        return $value;
    }

    /**
     * Set the value for the field.
     *
     * @param LaramoreModel|array|\ArrayAccess $model
     * @param  mixed                            $value
     * @return mixed
     */
    public function set($model, $value)
    {
        if (!$model->fetchingDatabase) {
            $value = $this->hash($value);
        }

        return parent::set($model, $value);
    }

    /**
     * Hash the password so it is not retrievible.
     *
     * @param string $value
     * @return string
     */
    public function hash(string $value)
    {
        return Hash::make($value);
    }

    /**
     * Indicate if the password is the right one.
     *
     * @param string  $value
     * @param string  $password
     * @param boolean $expected
     * @return boolean
     */
    public function isCorrect(string $value, string $password=null, bool $expected=true)
    {
        return Hash::check($password, $value) === $expected;
    }
}
