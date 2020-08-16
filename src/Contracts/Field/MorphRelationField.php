<?php
/**
 * Multiple relation field contract.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Contracts\Field;

use Laramore\Contracts\Field\Constraint\Constraint;
use Laramore\Elements\OperatorElement;

interface MorphRelationField extends RelationField
{
    /**
     * Return the target of the relation.
     *
     * @param string $className
     * @return Constraint
     */
    public function getTarget(string $className=null): Constraint;

    /**
     * Return the targets of the relation.
     *
     * @return array<Constraint>
     */
    public function getTargets(): array;

    /**
     * Models where the relation is set to.
     *
     * @return array<string>
     */
    public function getTargetModels(): array;

    /**
     * Return morph operators.
     *
     * @param OperatorElement $operator
     * @return array<OperatorElement>
     */
    public function getMorphOperators(OperatorElement $operator): array;
}
