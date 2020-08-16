<?php
/**
 * Define a field constraint.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields\Constraint;

use Laramore\Contracts\Field\Constraint\{
    IndexableConstraint, RelationalConstraint
};
use Laramore\Contracts\Field\AttributeField;
use Laramore\Exceptions\LockException;

abstract class BaseRelationalConstraint extends BaseConstraint implements RelationalConstraint
{
    /**
     * All relational constraint types.
     */
    const FOREIGN = 'foreign';
    const MORPH = 'morph';

    /**
     * Indexable constraint that is targeted.
     *
     * @var IndexableConstraint
     */
    protected $target;

    /**
     * Return indexable constraint.
     *
     * @param IndexableConstraint $target
     * @return self
     */
    public function setTarget(IndexableConstraint $target)
    {
        $this->needsToBeUnlocked();

        $this->target = $target;

        return $this;
    }

    /**
     * Return indexable constraint.
     *
     * @return IndexableConstraint
     */
    public function getTarget(): IndexableConstraint
    {
        return $this->target;
    }

    /**
     * Return the attributes that points to another.
     *
     * @return array<AttributeField>
     */
    public function getSourceAttributes(): array
    {
        return $this->getAttributes();
    }

    /**
     * Return the attributes that is pointed by this foreign relation.
     *
     * @return array<AttributeField>
     */
    public function getTargetAttributes(): array
    {
        return $this->getTarget()->getAttributes();
    }

    /**
     * Return the attribute that points to another.
     *
     * @return AttributeField
     */
    public function getSourceAttribute(): AttributeField
    {
        return $this->getAttribute();
    }

    /**
     * Return the attribute that is pointed by this foreign relation.
     *
     * @return AttributeField
     */
    public function getTargetAttribute(): AttributeField
    {
        return $this->getTarget()->getAttribute();
    }

    /**
     * Source and target attributes cannot intersect.
     *
     * @return void
     */
    protected function locking()
    {
        if (\count(\array_intersect($this->getSourceAttributes(), $this->getTargetAttributes()))) {
            throw new LockException('Source and target attributes cannot intersect in a relational constraint', 'attributes');
        }
    }
}
