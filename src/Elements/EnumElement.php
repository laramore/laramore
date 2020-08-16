<?php
/**
 * Define a specific enum field element.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Elements;

class EnumElement extends Element
{
    /**
     * Create an enum with a specific name.
     *
     * @param string $name
     * @param string $native
     * @param string $description
     */
    public function __construct(string $name, string $native, string $description=null)
    {
        parent::__construct($name, $native);

        $this->set('description', $description);
    }
}
