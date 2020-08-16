<?php
/**
 * Define possible foreign field contract.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Contracts\Field\Constraint;

use Laramore\Contracts\Field\{
    Field, Constraint\IndexableConstraint
};

interface RelationalField extends Field
{
    /**
     * Define a foreign constraint.
     *
     * @param  string              $name
     * @param IndexableConstraint $target
     * @param  Field|array<Field>  $fields
     * @return self
     */
    public function foreign(string $name=null, IndexableConstraint $target, $fields=[]);
}
