<?php
/**
 * Handle all observers for a specific class.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Fields\Constraint;

use Laramore\Contracts\Field\{
    Field, Constraint\Constraint
};
use Laramore\Observers\BaseObserver;
use Laramore\Traits\IsOwned;

class FieldConstraintHandler extends BaseConstraintHandler
{
    use IsOwned;

    /**
     * The observable class.
     *
     * @var string
     */
    protected $observableClass = Field::class;

    /**
     * The observer class to use to generate.
     *
     * @var string
     */
    protected $observerClass = Constraint::class;

    /**
     * Field field.
     *
     * @var Field
     */
    protected $field;

    /**
     * Create a field handler for a specific field.
     *
     * @param Field $field
     */
    public function __construct(Field $field)
    {
        $this->observableClass = \get_class($field);
        $this->field = $field;
    }

    /**
     * Return the field field.
     *
     * @return Field
     */
    public function getField(): Field
    {
        return $this->field;
    }

    /**
     * Add an observer for a specific model event.
     *
     * @param BaseObserver $constraint
     * @return self
     */
    public function add(BaseObserver $constraint)
    {
        /** @var BaseConstraint $constraint */
        parent::add($constraint);

        if ($this->isOwned()) {
            $this->getOwner()->add($constraint);
        }

        $fields = $constraint->getFields();

        // The first field adds the new constraint to others.
        if ($this->getField() === \array_shift($fields)) {
            // Add all relations to other fields.
            foreach ($fields as $field) {
                $field->getConstraintHandler()->add($constraint);
            }
        }

        return $this;
    }

    /**
     * Define the name.
     *
     * @param string $name
     * @return void
     */
    protected function setName(string $name)
    {
        $fieldname = $this->getField()->getName();

        if ($fieldname !== $name) {
            throw new \LogicException("The field field `{$fieldname}` is not the same as `$name`");
        }

        $this->name = $name;
    }

    /**
     * Add all constraints during ownership.
     *
     * @return void
     */
    protected function owned()
    {
        foreach ($this->all() as $constraints) {
            foreach ($constraints as $constraint) {
                $this->getOwner()->add($constraint);
            }
        }
    }
}
