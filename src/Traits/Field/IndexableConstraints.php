<?php
/**
 * Add management for field constraints.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Traits\Field;

use Laramore\Contracts\Field\Field;
use Laramore\Fields\Constraint\BaseIndexableConstraint;
use Laramore\Fields\Constraint\Index;
use Laramore\Fields\Constraint\Primary;
use Laramore\Fields\Constraint\Unique;

trait IndexableConstraints
{
    /**
     * Define a primary constraint.
     *
     * @param  string             $name
     * @param  Field|array<Field> $fields
     * @return self
     */
    public function primary(string $name=null, $fields=[])
    {
        $this->needsToBeUnlocked();

        $this->getConstraintHandler()->add(
            Primary::constraint(\is_array($fields) ? [$this, ...$fields] : [$this, $fields], $name)
        );

        return $this;
    }

    /**
     * Define a index constraint.
     *
     * @param  string             $name
     * @param  Field|array<Field> $fields
     * @return self
     */
    public function index(string $name=null, $fields=[])
    {
        $this->needsToBeUnlocked();

        $this->getConstraintHandler()->add(
            Index::constraint(\is_array($fields) ? [$this, ...$fields] : [$this, $fields], $name)
        );

        return $this;
    }

    /**
     * Define a unique constraint.
     *
     * @param  string             $name
     * @param  Field|array<Field> $fields
     * @return self
     */
    public function unique(string $name=null, $fields=[])
    {
        $this->needsToBeUnlocked();

        $this->getConstraintHandler()->add(
            Unique::constraint(\is_array($fields) ? [$this, ...$fields] : [$this, $fields], $name)
        );

        return $this;
    }
}
