<?php
/**
 * Lock in first the Meta as the bootable method is launched at last.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Providers;

use Illuminate\Support\ServiceProvider;
use Laramore\Facades\Metas;

class LastProvider extends ServiceProvider
{
    /**
     * Lock in first the Meta as the bootable method is launched at last.
     *
     * @return void
     */
    public function boot()
    {
        Metas::lock();
    }
}
