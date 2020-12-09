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

use Laramore\Contracts\Eloquent\LaramoreModel;
use Laramore\Traits\Eloquent\HasLaramoreModel;
use Illuminate\Database\Eloquent\Model as Model;

abstract class BaseModel extends Model implements LaramoreModel
{
    use HasLaramoreModel;

     /**
     * The name of the "deleted_at at" column.
     *
     * @var string|null
     */
    const DELETED_AT = 'deleted_at';
}
