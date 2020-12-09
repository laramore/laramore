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

use Illuminate\Database\Eloquent\Relations\Pivot;
use Laramore\Contracts\Eloquent\LaramoreModel;
use Laramore\Traits\Eloquent\HasLaramoreModel;

abstract class BasePivot extends Pivot implements LaramoreModel
{
    use HasLaramoreModel;
}
