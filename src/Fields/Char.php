<?php
/**
 * Define a char field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Laramore\Validations\Length;
use Laramore\Elements\Type as ReturnedType;
use Type;

class Char extends Text
{
    protected $maxLength;

    /**
     * Set of rules.
     * Common to all string fields.
     *
     * @var integer
     */

    // If the string is too long, auto cut at the defined length
    public const CARACTERE_RESIZE = 1024;

    // If the string is too long, auto cut at the last word before the defined length
    public const WORD_RESIZE = 2048;

    // If the string is too long, auto cut at the last sentence before the defined length
    public const SENTENCE_RESIZE = 4096;

    // If the string is too long, auto cut and add dots
    public const DOTS_ON_RESIZING = 8192;

    // Default rules
    public const DEFAULT_CHAR = (self::CARACTERE_RESIZE | self::DEFAULT_TEXT);

    protected static $defaultRules = self::DEFAULT_CHAR;

    protected function __construct($rules=null)
    {
        parent::__construct($rules);

        $this->maxLength = Schema::getFacadeRoot()::$defaultStringLength;
    }

    public function getType(): ReturnedType
    {
        return Type::char();
    }

    public function getLength(): ?int
    {
        return $this->maxLength;
    }

    public function getPropertyKeys(): array
    {
        return array_merge([
            'length:maxLength'
        ], parent::getPropertyKeys());
    }

    protected function setValidations()
    {
        parent::setValidations();

        if (!\is_null($this->maxLength)) {
            $this->setValidation(Length::class)->maxLength($this->maxLength);
        }
    }

    protected function setProxies()
    {
        parent::setProxies();

        $this->setProxy('resize', []);
    }

    public function maxLength(int $maxLength)
    {
        $this->needsToBeUnlocked();

        if ($maxLength <= 0) {
            throw new \Exception('The max length must be a positive number');
        }

        $this->defineProperty('maxLength', $maxLength);

        return $this;
    }

    public function transform($value)
    {
        $value = parent::transform($value);

        if ($this->maxLength < strlen($value) && !is_null($value)) {
            $dots = $this->hasRule(self::DOTS_ON_RESIZING) ? '...' : '';

            if ($this->hasRule(self::CARACTERE_RESIZE)) {
                $value = $this->resize($model, $attValue, $value, null, '', $dots);
            } else if ($this->hasRule(self::WORD_RESIZE)) {
                $value = $this->resize($model, $attValue, $value, null, ' ', $dots);
            } else if ($this->hasRule(self::SENTENCE_RESIZE)) {
                $value = $this->resize($model, $attValue, $value, null, '.', $dots);
            }
        }

        return $value;
    }

    public function resize(string $value, $length=null, $delimiter='', $toAdd='...')
    {
        $parts = $delimiter === '' ? str_split($value) : explode($delimiter, $value);
        $valides = [];
        $length = (($length ?: $this->maxLength) - strlen($toAdd));

        foreach ($parts as $part) {
            if (strlen($part) <= $length) {
                $length -= strlen($part);
                $valides[] = $part;
            } else {
                break;
            }
        }

        return implode($delimiter, $valides).$toAdd;
    }

    public function serialize($value)
    {
        return $value;
    }
}
