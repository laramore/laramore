<?php
/**
 * Add option management.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Traits;

use Laramore\Elements\OptionElement;
use Laramore\Facades\Option;

trait HasOptions
{
    /**
     * All options defined for the object.
     *
     * @var array
     */
    protected $options = [];

    public function options(array $options)
    {
        $this->needsToBeUnlocked();

        $this->options = [];

        foreach ($options as $option) {
            $this->addOption($option);
        }

        return $this;
    }

    /**
     * Indicate if the resource has a option.
     *
     * @param  string|OptionElement $option
     * @return boolean
     */
    public function hasOption($option)
    {
        return isset($this->options[$option instanceof OptionElement ? $option->getName() : $option]);
    }

    /**
     * Add a option to the resource.
     *
     * @param string|OptionElement $option
     * @return self
     */
    protected function addOption($option)
    {
        $this->needsToBeUnlocked();

        if (\is_string($option)) {
            $option = Option::get($option);
        }

        if (!$this->hasOption($option)) {
            $this->options[$option->getName()] = $option;

            foreach ($option->adds as $add) {
                $this->addOption($add);
            }

            foreach ($option->removes as $remove) {
                $this->removeOption($remove);
            }
        }

        return $this;
    }

    /**
     * Add multiple options to the resource.
     *
     * @param array $options
     * @return self
     */
    public function addOptions(array $options)
    {
        foreach ($options as $option) {
            $this->addOption($option);
        }

        return $this;
    }

    /**
     * Remove a option from the resource.
     *
     * @param  string|OptionElement $option
     * @return self
     */
    protected function removeOption($option)
    {
        $this->needsToBeUnlocked();

        if ($this->hasOption($option)) {
            unset($this->options[$option instanceof OptionElement ? $option->getName() : $option]);
        }

        return $this;
    }

    /**
     * List all current options for this resource.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
