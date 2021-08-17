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

use Laramore\Observers\{
    BaseObserver, BaseHandler
};
use Laramore\Fields\Constraint\{
    Primary, BaseConstraint
};
use Laramore\Exceptions\LockException;

abstract class BaseConstraintHandler extends BaseHandler
{
    /**
     * The observer class to use to generate.
     *
     * @var string
     */
    protected $observerClass = BaseConstraint::class;

    /**
     * Return if an observe exists with the given name.
     *
     * @param  string $name
     * @param  string $type
     * @return boolean
     */
    public function has(string $name, string $type=null): bool
    {
        if (\is_null($type)) {
            foreach ($this->observers as $types) {
                foreach ($types as $observer) {
                    if ($observer->getName() == $name) {
                        return true;
                    }
                }
            }
        } else {
            foreach ($this->observers[$type] as $observer) {
                if ($observer->getName() == $name) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Return the first observer with the given name.
     *
     * @param  string $name
     * @param  string $type
     * @return BaseObserver
     */
    public function get(string $name, string $type=null)
    {
        if (\is_null($type)) {
            foreach ($this->observers as $types) {
                foreach ($types as $observer) {
                    if ($observer->getName() == $name) {
                        return $observer;
                    }
                }
            }
        } else {
            foreach ($this->observers[$type] as $observer) {
                if ($observer->getName() == $name) {
                    return $observer;
                }
            }
        }

        throw new \Exception("The observer `$name` does not exist");
    }

    /**
     * Return the number of the handled observers.
     *
     * @param  string $type
     * @return integer
     */
    public function count(string $type=null): int
    {
        return \count($this->all($type));
    }

    /**
     * Return the list of constraints.
     *
     * @param  string $type
     * @return array<string,array<BaseObserver>>|array<BaseObserver>
     */
    public function all(string $type=null): array
    {
        if (\is_null($type)) {
            return $this->observers;
        } else {
            return ($this->observers[$type] ?? []);
        }
    }

    /**
     * Return the list of all constraints.
     *
     * @return array<BaseObserver>
     */
    public function getConstraints(): array
    {
        return \array_merge(...\array_values($this->all()));
    }

    /**
     * Push a constraint to a list of constraints.
     *
     * @param BaseObserver        $constraint
     * @param array<BaseObserver> $constraints
     * @return self
     */
    protected function push(BaseObserver $constraint, array &$constraints)
    {
        /** @var BaseConstraint $constraint */
        $type = $constraint->getConstraintType();

        if (!isset($constraints[$type])) {
            $constraints[$type] = [];
        }

        /** @var array<string,array<BaseConstraint>> $constraints */
        if (!\in_array($constraint, $constraints[$type])) {
            \array_push($constraints[$type], $constraint);
        }

        return $this;
    }

    /**
     * Actions during locking.
     *
     * @return void
     */
    protected function locking()
    {
        parent::locking();

        if ($this->count(BaseIndexableConstraint::PRIMARY) > 1) {
            throw new LockException('A field cannot have multiple primary constraints', 'primary');
        }
    }

    /**
     * Return the primary constraint.
     *
     * @return Primary|null
     */
    public function getPrimary()
    {
        $primaries = $this->all(BaseIndexableConstraint::PRIMARY);

        if (\count($primaries)) {
            return $primaries[0];
        }

        return null;
    }

    /**
     * Return all indexes.
     *
     * @return array<BaseIndexableConstraint>
     */
    public function getIndexes(): array
    {
        return $this->all(BaseIndexableConstraint::INDEX);
    }

    /**
     * Return all unique constraints.
     *
     * @return array<BaseIndexableConstraint>
     */
    public function getUniques(): array
    {
        return $this->all(BaseIndexableConstraint::UNIQUE);
    }

    /**
     * Return all foreign constraints.
     *
     * @return array<BaseRelationalConstraint>
     */
    public function getForeigns(): array
    {
        return $this->all(BaseRelationalConstraint::FOREIGN);
    }
}
