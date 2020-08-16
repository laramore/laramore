<?php
/**
 * Define a field option manager used by Laramore.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Elements;

use Laramore\Contracts\Manager\LaramoreManager;

class OptionManager extends ElementManager implements LaramoreManager
{
    protected $elementClass = OptionElement::class;
}
