<?php
/**
 * Define the validation manager class.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Validations;

use Illuminate\Database\Eloquent\Model;
use Laramore\Observers\BaseManager;

class ValidationManager extends BaseManager
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
    protected $handlerClass = ValidationHandler::class;
}
