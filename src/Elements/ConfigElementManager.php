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
            if (!isset($elements[$key])) {
                $elements[$key] = [];
            }
            dump($elements);
            $elements[$key] = \array_merge($elements[$key], config($this->configPath.'.'.$key.'.', []));
        }

        parent::__construct($elements);
    }
}
