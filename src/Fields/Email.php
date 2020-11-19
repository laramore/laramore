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

class Email extends Char implements PatternField, FixableField
{
    /**
     * All defined allowed domains.
     *
     * @var array
     */
    protected $allowedDomains;

    /**
     * Define the allowed domains.
     *
     * @param array $allowedDomains
     * @return self
     */
    public function allowedDomains(array $allowedDomains)
    {
        $this->needsToBeUnlocked();

        foreach ($allowedDomains as $allowedDomain) {
            if (!\preg_match($this->getConfig('patterns.domain'), $allowedDomain)) {
                throw new \Exception("`$allowedDomain` is not a right domain");
            }
        }

        $this->defineProperty('allowedDomains', $allowedDomains);

        return $this;
    }

    /**
     * Return the main domain.
     *
     * @return string
     */
    public function getMainDomain(): string
    {
        $allowedDomains = $this->getAllowedDomains();

        return \reset($allowedDomains);
    }

    /**
     * Return the username pattern.
     *
     * @return string
     */
    public function getUsernamePattern(): string
    {
        return $this->getConfig('patterns.username');
    }

    /**
     * Return the domain pattern.
     *
     * @return string
     */
    public function getDomainPattern(): string
    {
        return $this->getConfig('patterns.domain');
    }

    /**
     * Return the pattern to match.
     *
     * @return string
     */
    public function getPattern(): string
    {
        return $this->getConfig('patterns.email');
    }

    /**
     * Return all pattern flags
     *
     * @return mixed
     */
    public function getPatternFlags()
    {
        return $this->getConfig('patterns.flags');
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

        if ($this->hasOption(Option::fixable())
            && (\is_null($this->getProperty('allowedDomains')) || \count($this->getProperty('allowedDomains')) === 0)
        ) {
            throw new \LogicException("The field `{$this->getQualifiedName()}` cannot be fixable and have no allowed domains");
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

        return $value.$this->getConfig('patterns.separator').$this->getMainDomain();
    }
}
