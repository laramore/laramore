<?php
/**
 * Proxy contract.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Contracts;

interface Proxied
{
    /**
     * Call a proxy by its name.
     *
     * @param mixed $name
     * @param mixed $args
     * @return mixed
     */
    public function __proxy($name, $args);

    /**
     * Return a static proxy by its name.
     *
     * @param mixed $name
     * @param mixed $args
     * @return mixed
     */
    public static function __proxyStatic($name, $args);
}
