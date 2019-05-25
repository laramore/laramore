<?php
/**
 * Create an Observer to add a callback on a specific Grammar.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Observers;

use Laramore\Traits\IsLocked;
use Closure;

class GrammarObserver extends BaseObserver
{
    /**
     * Actions during locking.
     *
     * @return void
     */
    protected function locking()
    {
        // Nothing to do here.
    }
}
