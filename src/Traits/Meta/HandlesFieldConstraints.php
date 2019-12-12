<?php
/**
 * Handle field constraints.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Traits\Meta;

use Laramore\Fields\{
    AttributeField, CompositeField
};
use Laramore\Fields\Constraint\{
    Primary, Index, Unique, Foreign
};

trait HandlesFieldConstraints
{
    public function getFieldsFromArray(array $array)
    {
        $fields = [];

        foreach ($array as $element) {
            $field = \is_string($element) ? $this->getField($element) : $element;

            if ($field instanceof CompositeField) {
                $fields = \array_merge($fields, $field->getAttributes());
            } else if ($field instanceof AttributeField) {
                $fields[] = $field;
            } else {
                throw new \LogicException('Link fields does not have fields');
            }
        }

        return \array_unique($fields, SORT_REGULAR);
    }

    /**
     * Define a primary constraint.
     *
     * @param  string|array $fields
     * @param  string       $name
     * @param  string       $class
     * @param  integer      $priority
     * @return self
     */
    public function primary($fields, string $name=null, string $class=null, int $priority=Primary::MEDIUM_PRIORITY)
    {
        $this->needsToBeUnlocked();

        $fields = $this->getFieldsFromArray(\is_array($fields) ? $fields : [$fields]);

        if (\is_null($class)) {
            $class = config('fields.constraints.types.primary.class');
        }

        $this->getConstraintHandler()->add($class::constraint($fields, $name, $priority));

        return $this;
    }

    /**
     * Define an index constraint.
     *
     * @param  string|array $fields
     * @param  string       $name
     * @param  string       $class
     * @param  integer      $priority
     * @return self
     */
    public function index($fields, string $name=null, string $class=null, int $priority=Index::MEDIUM_PRIORITY)
    {
        $this->needsToBeUnlocked();

        $fields = $this->getFieldsFromArray(\is_array($fields) ? $fields : [$fields]);

        if (\is_null($class)) {
            $class = config('fields.constraints.types.index.class');
        }

        $this->getConstraintHandler()->add($class::constraint($fields, $name, $priority));

        return $this;
    }

    /**
     * Define a unique constraint.
     *
     * @param  string|array $fields
     * @param  string       $name
     * @param  string       $class
     * @param  integer      $priority
     * @return self
     */
    public function unique($fields, string $name=null, string $class=null, int $priority=Unique::MEDIUM_PRIORITY)
    {
        $this->needsToBeUnlocked();

        $fields = $this->getFieldsFromArray(\is_array($fields) ? $fields : [$fields]);

        if (\is_null($class)) {
            $class = config('fields.constraints.types.unique.class');
        }

        $this->getConstraintHandler()->add($class::constraint($fields, $name, $priority));

        return $this;
    }

    /**
     * Define a foreign constraint.
     *
     * @param  string|array $fields
     * @param  string       $name
     * @param  string       $class
     * @param  integer      $priority
     * @return self
     */
    public function foreign($fields, string $name=null, string $class=null, int $priority=Unique::MEDIUM_PRIORITY)
    {
        $this->needsToBeUnlocked();

        $fields = $this->getFieldsFromArray(\is_array($fields) ? $fields : [$fields]);

        if (\is_null($class)) {
            $class = config('fields.constraints.types.foreign.class');
        }

        $this->getConstraintHandler()->add($class::constraint($fields, $name, $priority));

        return $this;
    }

    public function getPrimary(): ?Primary
    {
        return $this->getConstraintHandler()->getPrimary();
    }

    public function getIndexes(): array
    {
        return $this->getConstraintHandler()->getIndexes();
    }

    public function getUniques(): array
    {
        return $this->getConstraintHandler()->getUniques();
    }

    public function getForeigns(): array
    {
        return $this->getConstraintHandler()->getForeigns();
    }
}
