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

use Laramore\Contracts\Field\Constraint\IndexableConstraint;
use Laramore\Exceptions\LockException;

abstract class BaseIndexableConstraint extends BaseConstraint implements IndexableConstraint
{
    /**
     * Model class used.
     *
     * @var string
     */
    protected $modelClass;

    /**
     * All indexable constraint types.
     */
    const PRIMARY = 'primary';
    const INDEX = 'index';
    const UNIQUE = 'unique';
    const MORPH_INDEX = 'morph_index';

    /**
     * Define migrable constraints.
     *
     * @var array
     */
    public static $migrable = [
        self::PRIMARY, self::INDEX, self::UNIQUE,
    ];

    /**
     * Return the model class used for this constraint.
     *
     * @return string
     */
    public function getModelClass(): string
    {
        return $this->modelClass;
    }

    /**
     * Check that this constraints works only on one model.
     *
     * @return void
     */
    protected function locking()
    {
        $this->modelClass = $this->getFields()[0]->getMeta()->getModelClass();

        foreach ($this->getFields() as $field) {
            if ($field->getMeta()->getModelClass() !== $this->modelClass) {
                throw new LockException('An indexable constraint can only be from a single model', 'modelClass');
            }
        }
    }

    /**
     * Call the constraint by find the right value.
     *
     * @param mixed ...$args
     * @return void
     */
    public function __invoke(...$args)
    {
    }
}
