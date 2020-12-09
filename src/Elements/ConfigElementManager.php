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
        parent::__construct(config($this->configPath));
    }
}
