<?php
/**
 * Merge config files.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Traits\Provider;

use Illuminate\Support\Arr;
use Laramore\Exceptions\ConfigException;

trait MergesConfig
{
    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param  mixed $path
     * @param  mixed $key
     * @return void
     */
    protected function mergeConfigFrom($path, $key)
    {
        $config = $this->app->config->get($key, []);

        $this->app->config->set($key, $this->mergeConfig(require $path, $config, [$key]));
    }

    /**
     * Merges the configs together and takes multi-dimensional arrays into count.
     *
     * @param  array $original
     * @param  array $merging
     * @param  array $path
     * @return array
     */
    protected function mergeConfig(array $original, array $merging, array $path=[]): array
    {
        if (Arr::isAssoc($original) != Arr::isAssoc($merging)
            && \count(\array_values($original)) && \count(\array_values($merging))) {
            throw new ConfigException(\implode('.', $path), null, null, 'Can\'t merge. Check the configuration from Laramore.');
        }

        if (Arr::isAssoc($original)) {
            foreach ($original as $key => $value) {
                if (Arr::exists($merging, $key)) {
                    if (\is_array($value) && \is_array($merging[$key])) {
                        $original[$key] = $this->mergeConfig($value, $merging[$key], \array_merge($path, [$key]));
                    } else {
                        $original[$key] = $merging[$key];
                    }

                    unset($merging[$key]);
                }
            }

            return \array_merge($merging, $original);
        } else {
            return $merging;
        }
    }
}
