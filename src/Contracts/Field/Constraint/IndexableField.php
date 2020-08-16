<?php
/**
 * Define a unique field contract.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Contracts\Field\Constraint;

use Laramore\Contracts\Field\Field;

interface IndexableField extends Field
{
    /**
     * Define a primary constraint.
     *
     * @param  string             $name
     * @param  Field|array<Field> $fields
     * @return self
     */
    public function primary(string $name=null, $fields=[]);

    /**
     * Define a index constraint.
     *
     * @param  string             $name
     * @param  Field|array<Field> $fields
     * @return self
     */
    public function index(string $name=null, $fields=[]);

    /**
     * Define a unique constraint.
     *
     * @param  string             $name
     * @param  Field|array<Field> $fields
     * @return self
     */
    public function unique(string $name=null, $fields=[]);
}
