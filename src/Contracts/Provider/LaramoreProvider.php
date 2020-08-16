<?php
/**
 * Interface for all providers defining a manager for Laramore.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Contracts\Provider;

use Laramore\Contracts\Manager\LaramoreManager;

interface LaramoreProvider
{
    /**
     * Publish the config linked to the manager.
     *
     * @return void
     */
    public function boot();

    /**
     * Register our facade and create the manager.
     *
     * @return void
     */
    public function register();

    /**
     * Return the default values for the manager of this provider.
     *
     * @return mixed
     */
    public static function getDefaults();

    /**
     * Generate the corresponded manager.
     *
     * @return LaramoreManager
     */
    public static function generateManager(): LaramoreManager;
}
