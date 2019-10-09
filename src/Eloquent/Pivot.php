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

use Illuminate\Database\Eloquent\Relations\Pivot as BasePivot;
use Laramore\Interfaces\IsALaramoreModel;
use Laramore\Traits\Pivot\HasLaramore;

abstract class Pivot extends BasePivot implements IsALaramoreModel
{
    use HasLaramore;
}
