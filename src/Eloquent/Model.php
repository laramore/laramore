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

use Laramore\Traits\Model\HasLaramore;
use Laramore\Interfaces\IsALaramoreModel;
use Illuminate\Database\Eloquent\Model as BaseModel;

abstract class Model extends BaseModel implements IsALaramoreModel
{
    use HasLaramore;
}
