<?php
/**
 * Handle all observers for a specific model.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Observers;

use Illuminate\Database\Eloquent\Model;

class ModelObservableManager extends BaseObservableManager
{
    protected $observableSubClass = Model::class;
    protected $observableHandlerClass = ModelObservableHandler::class;
}
