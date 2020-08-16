<?php
/**
 * Define an increment field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

use Laramore\Contracts\Field\IncrementField;
use Laramore\Elements\TypeElement;
use Laramore\Traits\Field\Increments;

class Increment extends Integer implements IncrementField
{
    use Increments;

    /**
     * Return the type object of the field.
     *
     * @return TypeElement
     */
    public function getType(): TypeElement
    {
        return $this->resolveType();
    }
}
