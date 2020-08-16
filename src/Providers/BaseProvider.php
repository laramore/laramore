<?php
/**
 * Add required macros and all base for Laramore.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Providers;

use Illuminate\Support\{
    ServiceProvider, Str
};

class BaseProvider extends ServiceProvider
{
    /**
     * During booting, add our macro.
     *
     * @return void
     */
    public function register()
    {
        $this->app->booting([$this, 'booting']);
    }

    /**
     * Add macro.
     *
     * @return void
     */
    public function booting()
    {
        Str::macro('replaceInTemplate', function (string $template, array $keyValues): string
        {
            foreach ($keyValues as $key => $value) {
                $template = \str_replace([
                    '${'.$key.'}', '#{'.$key.'}', '+{'.$key.'}', '_{'.$key.'}', '-{'.$key.'}',
                    '$^{'.$key.'}', '#^{'.$key.'}', '+^{'.$key.'}', '_^{'.$key.'}', '-^{'.$key.'}',
                ], [
                    $value, Str::singular($value), Str::plural($value), Str::snake($value), Str::camel($value),
                    \ucwords($value), Str::singular(\ucwords($value)), Str::plural(\ucwords($value)),
                    \ucwords(Str::snake($value)), \ucwords(Str::camel($value))
                ], $template);
            }

            return $template;
        });
    }
}
