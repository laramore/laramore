<?php
/**
 * Define a unique id field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Fields;

use Laramore\Contracts\Field\Constraint\UniqueField;
use Laramore\Fields\Constraint\UniqueConstraintHandler;

class UniqueId extends Integer implements UniqueField
{
    /**
     * Create a Constraint handler for this meta.
     *
     * @return void
     */
    protected function setConstraintHandler()
    {
        $this->constraintHandler = new UniqueConstraintHandler($this);
    }

    /**
     * Return the relation handler for this meta.
     *
     * @return UniqueConstraintHandler
     */
    public function getConstraintHandler(): UniqueConstraintHandler
    {
        return parent::getConstraintHandler();
    }

    /**
     * Define a unique constraint.
     *
     * @param  string             $name
     * @param  Field|array<Field> $fields
     * @return self
     */
    public function unique(string $name=null, $fields=null)
    {
        $this->needsToBeUnlocked();

        if (!\is_null($fields) && (\is_array($fields) || \count($fields) > 0)) {
            throw new \LogicException('An incremental field cannot have co-unique fields');
        }

        $this->getConstraintHandler()->getUniques()[0]->setName($name);

        return $this;
    }
}
