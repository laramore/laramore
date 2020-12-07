<?php
/**
 * Load elements from configs.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Elements;

use Laramore\Exceptions\ConfigException;


abstract class LazyElementManager extends ElementManager
{
    /**
     * Indicate if an element exists with the given name.
     *
     * @param  string $name
     * @return boolean
     */
    public function has(string $name): bool
    {
        return parent::has($name) || config()->has("{$this->configPath}.$name");
    }

    /**
     * Returns the element with the given name.
     *
     * @param  string $name
     * @return Element
     * @throws \ErrorException If no element exists with this name.
     */
    public function get(string $name): Element
    {
        if (!parent::has($name)) {
            $path = "{$this->configPath}.$name";

            if (!config()->has($path)) {
                throw new ConfigException($path, ['array of values']);
            }

            $this->create($name, config($path));
        }

        return $this->elements[$name];
    }
}
