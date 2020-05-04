<?php
/**
 * Meta has field constraints.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Traits\Eloquent;

use Laramore\Contracts\Field\{
    AttributeField, ComposedField, Constraint\Constrainted
};

trait HasFieldsConstraints
{
    /**
     * Extract all attributes from fields.
     *
     * @param array<Field|string> $fields
     * @return array<AttributeField>
     */
    protected function extractAttributes($fields)
    {
        $attributes = [];
        $fields = \is_array($fields) ? $fields : [$fields];

        foreach ($fields as $field) {
            if ($field instanceof AttributeField) {
                $attributes[] = $field;
            } else if ($field instanceof ComposedField) {
                $attributes = \array_merge($attributes, $field->getFields(AttributeField::class));
                $fields = \array_merge($fields, $field->getFields(CompositeField::class));
            } else if (\is_string($field)) {
                $attributes[] = $this->getField($field);
            } else {
                throw new \LogicException('Only composed and attribute fields are allowed in constraints');
            }
        }

        return \array_unique($attributes, SORT_REGULAR);
    }

    /**
     * Define a primary constraint.
     *
     * @param  Constrainted|array<Constrainted> $constrainted
     * @param  string                           $name
     * @return self
     */
    public function primary($constrainted, string $name=null)
    {
        $this->needsToBeUnlocked();

        $constrainted = $this->extractAttributes($constrainted);
        $carrier = \array_shift($constrainted);

        $carrier->primary($name, $constrainted);

        return $this;
    }

    /**
     * Define an index constraint.
     *
     * @param  Constrainted|array<Constrainted> $constrainted
     * @param  string                           $name
     * @return self
     */
    public function index($constrainted, string $name=null)
    {
        $this->needsToBeUnlocked();

        $constrainted = $this->extractAttributes($constrainted);
        $carrier = \array_shift($constrainted);

        $carrier->index($name, $constrainted);

        return $this;
    }

    /**
     * Define a unique constraint.
     *
     * @param  Constrainted|array<Constrainted> $constrainted
     * @param  string                           $name
     * @return self
     */
    public function unique($constrainted, string $name=null)
    {
        $this->needsToBeUnlocked();

        $constrainted = $this->extractAttributes($constrainted);
        $carrier = \array_shift($constrainted);

        $carrier->unique($name, $constrainted);

        return $this;
    }
}
