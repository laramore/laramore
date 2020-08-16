<?php
/**
 * Add multiple methods for one to many/one relations.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Traits\Field;

use Laramore\Facades\Operator;
use Laramore\Contracts\Eloquent\LaramoreBuilder;

trait HasOneRelation
{
    use ModelRelation, ReversedRelation, IndexableConstraints, RelationalConstraints;

    /**
     * Cast the value in the correct format.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function cast($value)
    {
        $modelClass = $this->getTargetModel();

        if (\is_null($value) || ($value instanceof $modelClass)) {
            return $value;
        }

        return new $modelClass($value);
    }

    /**
     * Serialize the value for outputs.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function serialize($value)
    {
        return $value;
    }

    /**
     * Add a where null condition from this field.
     *
     * @param  LaramoreBuilder $builder
     * @param  string          $boolean
     * @param  boolean         $not
     * @param  \Closure        $callback
     * @return LaramoreBuilder
     */
    public function whereNull(LaramoreBuilder $builder, string $boolean='and',
                              bool $not=false, \Closure $callback=null): LaramoreBuilder
    {
        if ($not) {
            return $this->whereNotNull($builder, $boolean, null, 1, $callback);
        }

        return $builder->doesntHave($this->getName(), $boolean, $callback);
    }

    /**
     * Add a where not null condition from this field.
     *
     * @param  LaramoreBuilder $builder
     * @param  string          $boolean
     * @param  mixed           $operator
     * @param  integer         $count
     * @param  \Closure        $callback
     * @return LaramoreBuilder
     */
    public function whereNotNull(LaramoreBuilder $builder, string $boolean='and',
                                 $operator=null, int $count=1, \Closure $callback=null): LaramoreBuilder
    {
        return $builder->has($this->getName(), (string) ($operator ?? Operator::supEq()), $count, $boolean, $callback);
    }
}
