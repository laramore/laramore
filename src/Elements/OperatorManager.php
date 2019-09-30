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

class OperatorManager extends BaseManager
{
    protected $elementClass = Operator::class;

    /**
     * Build default elements managed by this manager.
     *
     * @param array $defaults
     */
    public function __construct(array $defaults=[])
    {
        foreach ($defaults as $name => $native) {
            if (\is_array($native)) {
                $this->elements[$name] = $element = new $this->elementClass($name, ...$native);
            } else {
                $this->elements[$name] = new $this->elementClass($name, $native);
            }
        }
    }
}
