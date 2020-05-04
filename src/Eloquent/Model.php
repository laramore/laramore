<?php
/**
 * Use the Laramore engine with the Eloquent model.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Eloquent;

use Laramore\Traits\Eloquent\HasLaramoreModel;
use Laramore\Contracts\Eloquent\LaramoreModel;
use Illuminate\Database\Eloquent\Model as BaseModel;

abstract class Model extends BaseModel implements LaramoreModel
{
    use HasLaramoreModel;
}
