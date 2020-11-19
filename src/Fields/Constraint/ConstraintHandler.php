<?php
/**
 * Handle all observers for a specific class.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields\Constraint;

use Laramore\Contracts\Eloquent\LaramoreModel;
use Laramore\Contracts\Field\Constraint\Constraint;


class ConstraintHandler extends BaseConstraintHandler
{
    /**
     * The observable class.
     *
     * @var string
     */
    protected $observableClass = LaramoreModel::class;

    /**
     * All field constraints
     *
     * @var array<FieldConstraintHandler>
     */
    protected $fieldConstraints = [];

    /**
     * Add a field handler
     *
     * @param FieldConstraintHandler $handler
     * @return self
     */
    public function addFieldHandler(FieldConstraintHandler $handler)
    {
        $name = $handler->getField()->getName();

        $this->fieldConstraints[$name] = $handler->ownedBy($this, $name);

        return $this;
    }

    /**
     * Indicate if it has a field handler.
     *
     * @param string $name
     * @return boolean
     */
    public function hasFieldHandler(string $name): bool
    {
        return isset($this->fieldConstraints[$name]);
    }

    /**
     * Return a field handler by the field name.
     *
     * @param string $name
     * @return FieldConstraintHandler
     */
    public function getFieldHandler(string $name): FieldConstraintHandler
    {
        return $this->fieldConstraints[$name];
    }

    /**
     * Return all field handlers.
     *
     * @return array
     */
    public function getFieldHandlers(): array
    {
        return $this->fieldConstraints;
    }

    /**
     * Need to lock every observer.
     *
     * @return void
     */
    protected function locking()
    {
        foreach ($this->observers as $constraints) {
            foreach ($constraints as $constraint) {
                if (!$constraint->isLocked()) {
                    $constraint->lock();
                }
            }
        }
    }
}
