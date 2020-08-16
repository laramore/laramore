<?php
/**
 * Define a field type manager used by Laramore.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Elements;

use Laramore\Contracts\Manager\LaramoreManager;

class TypeManager extends ElementManager implements LaramoreManager
{
    protected $elementClass = TypeElement::class;

    /**
     * Lock every element.
     *
     * @return void
     */
    protected function locking()
    {
        foreach ($this->all() as $element) {
            $element->inherit();
        }

        parent::locking();
    }
}
