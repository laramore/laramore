<?php
/**
 * Define an operator manager used for SQL operations.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Elements;

use Laramore\Contracts\Manager\LaramoreManager;

class OperatorManager extends ElementManager implements LaramoreManager
{
    protected $elementClass = OperatorElement::class;
}
