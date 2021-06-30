<?php
/**
 * Use the Laramore engine with the Eloquent pivot.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Eloquent;

use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;
use Laramore\Contracts\Eloquent\LaramorePivot;

abstract class BasePivot extends BaseModel implements LaramorePivot
{
    use AsPivot;
}
