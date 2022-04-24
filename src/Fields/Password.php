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

use Laramore\Contracts\Eloquent\LaramoreModel;
use Laramore\Facades\Option;
use Laramore\Contracts\Field\PatternField;

class Password extends Hashed implements PatternField
{
    /**
     * Min length for a password.
     *
     * @var int
     */
    protected $minLength;

    /**
     * Max length for a password.
     *
     * @var int
     */
    protected $maxLength;

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
}
