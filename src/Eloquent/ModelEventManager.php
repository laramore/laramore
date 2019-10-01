<?php
/**
 * Handle all observers for a specific model.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Laramore\Observers\BaseManager;

class ModelEventManager extends BaseManager
{
    /**
     * Allowed observable sub class.
     *
     * @var string
     */
    protected $managedClass = Model::class;

    /**
     * The observable handler class to generate.
     *
     * @var string
     */
    protected $handlerClass = ModelEventHandler::class;
}
