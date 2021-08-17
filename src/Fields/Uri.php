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

use Laramore\Facades\Option;
use Laramore\Contracts\Field\{
    PatternField, FixableField
};

class Uri extends Char implements PatternField, FixableField
{
    /**
     * All defined allowed protocols.
     *
     * @var array
     */
    protected $allowedProtocols;

    /**
     * All patterns defined for this field.
     *
     * @var array
     */
    protected $patterns;

    /**
     * Define the allowed protocols.
     *
     * @param array $allowedProtocols
     * @return self
     */
    public function allowedProtocols(array $allowedProtocols)
    {
        $this->needsToBeUnlocked();

        foreach ($allowedProtocols as $allowedProtocol) {
            if (!\preg_match($this->patterns['protocol'], $allowedProtocol)) {
                throw new \Exception("`$allowedProtocol` is not a right protocol");
            }
        }

        $this->defineProperty('allowedProtocols', $allowedProtocols);

        return $this;
    }

    /**
     * Return the main protocol.
     *
     * @return string
     */
    public function getMainProtocol(): string
    {
        $allowedProtocols = $this->getAllowedProtocols();

        return \reset($allowedProtocols);
    }

    /**
     * Return the protocol pattern.
     *
     * @return string
     */
    public function getProtocolPattern(): string
    {
        return $this->patterns['protocol'];
    }

    /**
     * Return the identifier pattern.
     *
     * @return string
     */
    public function getIdentifierPattern(): string
    {
        return $this->patterns['identifier'];
    }

    /**
     * Return the pattern to match.
     *
     * @return string
     */
    public function getPattern(): string
    {
        return $this->patterns['uri'];
    }

    /**
     * Return all pattern flags
     *
     * @return mixed
     */
    public function getPatternFlags()
    {
        return $this->patterns['flags'];
    }

    /**
     * Indicate if the value needs to be fixed.
     *
     * @param mixed $value
     * @return boolean
     */
    public function isFixable($value): bool
    {
        return $this->hasOption(Option::fixable()) && !\preg_match($this->getPattern(), $value);
    }

    /**
     * Check all properties and options before locking the field.
     *
     * @return void
     */
    protected function checkOptions()
    {
        parent::checkOptions();

        $name = $this->getQualifiedName();

        if ($this->hasOption(Option::fixable())
            && (\is_null($this->getProperty('allowedProtocols'))
                || \count($this->getProperty('allowedProtocols')) == 0)) {
            throw new \LogicException("The field `$name` cannot be fixable and have no allowed protocols");
        }
    }

    /**
     * Cast the value to correspond to the field desire.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function cast($value)
    {
        $value = parent::cast($value);

        if ($this->isFixable($value)) {
            return $this->fix($value);
        }

        return $value;
    }

    /**
     * Serialize the value for outputs.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function serialize($value)
    {
        return $value;
    }

    /**
     * Fix the wrong value.
     *
     * @param mixed $value
     * @return mixed
     */
    public function fix($value)
    {
        if (\is_null($value)) {
            return $value;
        }

        return $this->getMainProtocol().$value;
    }
}
