<?php
/**
 * Group all handlers in a manager.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields\Constraint;

use Laramore\Observers\BaseManager;
use Laramore\Contracts\{
    Manager\LaramoreManager, Eloquent\LaramoreModel
};

class ConstraintManager extends BaseManager implements LaramoreManager
{
    /**
     * Allowed observable sub class.
     *
     * @var string
     */
    protected $managedClass = LaramoreModel::class;

    /**
     * The observable handler class to generate.
     *
     * @var string
     */
    protected $handlerClass = ConstraintHandler::class;
}
