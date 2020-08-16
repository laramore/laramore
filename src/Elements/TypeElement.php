<?php
/**
 * Define a specific field type element.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Elements;

use Laramore\Facades\Type;
use Laramore\Traits\Provider\MergesConfig;

class TypeElement extends Element
{
    use MergesConfig;

    /**
     * Indicate if the type is inherited.
     *
     * @var bool
     */
    protected $inherited = false;

    /**
     * Each class locks in a specific way.
     *
     * @return self
     */
    public function inherit(): self
    {
        $this->needsToBeUnlocked();

        if ($this->has('parent') && $this->inherited === false) {
            $parentType = Type::get(parent::get('parent'));

            $this->values = $this->mergeConfig($parentType->inherit()->toArray(), $this->values, []);
            $this->inherited = true;
        }

        return $this;
    }
}
