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


abstract class ConfigElementManager extends ElementManager
{
    /**
     * Build default elements managed by this manager.
     */
    public function __construct()
    {
        $elements = config($this->configPath.'.elements', []);

        foreach (static::$configKeys as $key) {
            $keyElements = config($this->configPath.'.'.$key, []);

            foreach (\array_keys($elements) as $element) {
                if (isset($elements[$element][$key])) {
                    continue;
                }

                if (!isset($keyElements[$element])) {
                    throw new \Exception("No `$key` value were defined for {$this->configPath}: $element");
                }

                $elements[$element][$key] = $keyElements[$element];
            }
        }

        parent::__construct($elements);
    }
}
