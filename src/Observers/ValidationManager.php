<?php
/**
 * Define the validation manager class.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Observers;

use Illuminate\Database\Eloquent\Model;

class ValidationManager extends BaseObservableManager
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
    protected $observableHandlerClass = ValidationHandler::class;
}
