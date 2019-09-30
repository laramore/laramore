<?php
/**
 * Define an operator for SQL operations.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Elements;

class Operator extends BaseElement
{
    /**
     * Create the operator with a specific name.
     *
     * @param string $name
     * @param string $native
     * @param string $needs
     */
    public function __construct(string $name, string $native, string $needs=null)
    {
        parent::__construct($name, $native);

        $this->needs = $needs;
    }
}
