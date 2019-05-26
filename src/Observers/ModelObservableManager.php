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
    /**
     * Allowed observable sub class.
     *
     * @var string
     */
    protected $observableSubClass = Model::class;

    /**
     * The observable handler class to generate.
     *
     * @var string
     */
    protected $observableHandlerClass = ModelObservableHandler::class;
}
