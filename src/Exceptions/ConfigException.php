<?php
/**
 * This exception indicate that an issue was detected in configuration usage.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Exceptions;

class ConfigException extends LaramoreException
{
    /**
     * String value.
     *
     * @var string
     */
    protected $config;

    /**
     * Supported values for the config given.
     *
     * @var array<string>
     */
    protected $supportedValues;

    /**
     * Given value.
     *
     * @var mixed
     */
    protected $givenValue;

    /**
     * Create a new LaramoreException.
     *
     * @param string        $config
     * @param array<string> $supportedValues
     * @param mixed         $givenValue
     * @param string        $message
     * @param integer       $code
     * @param \Throwable    $previous
     */
    public function __construct(string $config, array $supportedValues=null, $givenValue=null, string $message=null,
        int $code=0, \Throwable $previous=null)
    {
        $this->config = $config;

        if (\is_null($message)) {
            $this->supportedValues = (array) $supportedValues;
            $this->givenValue = $givenValue;

            if (\is_array($supportedValues)) {
                if (\count($supportedValues) > 1) {
                    $message = 'It requires one of theses values: `'.\json_encode($supportedValues);
                } else {
                    $message = 'It requires: `'.\json_encode($supportedValues);
                }
            } else {
                $message = "It requires `$supportedValues`";
            }

            $message .= '`, got instead: `'.\json_encode($givenValue).'`';
        }

        parent::__construct("The config `$config` is incorrect. ".$message, $code, $previous);
    }

    /**
     * Return the configuration that has not a valid value.
     *
     * @return string
     */
    public function getConfig(): string
    {
        return $this->config;
    }

    /**
     * Return the supported values for the specific configuration.
     *
     * @return array<string>
     */
    public function getSupportedValues(): array
    {
        return $this->supportedValues;
    }

    /**
     * Return the given value for this configuration.
     *
     * @return mixed
     */
    public function getGivenValue()
    {
        return $this->givenValue;
    }
}
