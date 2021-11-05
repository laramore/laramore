<?php
/**
 * Define an attribute field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

use Illuminate\Support\{
    Str, Collection
};
use Laramore\Elements\OperatorElement;
use Laramore\Contracts\{
    Field\AttributeField, Eloquent\LaramoreBuilder
};
use Laramore\Facades\Operator;
use Laramore\Fields\Constraint\BaseRelationalConstraint;
use Laramore\Traits\Field\{
    IndexableConstraints, RelationalConstraints
};

abstract class BaseAttribute extends BaseField implements AttributeField
{
    use IndexableConstraints, RelationalConstraints;

    /**
     * AttributeField name of this field.
     *
     * @var string
     */
    protected $attname;

    /**
     * Parse the attribute attname.
     *
     * @param  string $attname
     * @return string
     */
    public static function parseAttname(string $attname): string
    {
        return Str::snake($attname);
    }

    /**
     * Get the attribute name.
     *
     * @return string
     */
    public function getAttname(): string
    {
        return $this->attname;
    }

    /**
     * Define the name property.
     *
     * @param  string $name
     * @param  string $attname
     * @return self
     */
    protected function setName(string $name, string $attname=null)
    {
        parent::setName($name);

        // If no attribute name have been set by the user, define ours based on the name.
        if (\is_null($this->attname)) {
            $this->setAttname(static::parseAttname($name));
        }

        return $this;
    }

    /**
     * Return the native value of this field.
     * Commonly, its attname.
     *
     * @return string
     */
    public function getNative(): string
    {
        return $this->attname;
    }

    /**
     * Each class locks in a specific way.
     *
     * @return void
     */
    protected function locking()
    {
        parent::locking();

        if ($this->getConstraintHandler()->count(BaseRelationalConstraint::FOREIGN) > 0) {
            foreach ($this->getConstraintHandler()->all(BaseRelationalConstraint::FOREIGN) as $constraint) {
                /** @var RelationConstraint $constraint */
                if ($constraint->getSourceAttribute() === $this) {
                    $this->addOptions(\array_merge($constraint->getTargetAttribute()->options, $this->options));
                }
            }
        }
    }

    /**
     * Add a where null condition from this field.
     *
     * @param  LaramoreBuilder $builder
     * @param  string          $boolean
     * @param  boolean         $not
     * @return LaramoreBuilder
     */
    public function whereNull(LaramoreBuilder $builder, string $boolean='and', bool $not=false): LaramoreBuilder
    {
        return $this->where($builder, $not ? Operator::notNull() : Operator::null(), null, $boolean, $not);
    }

    /**
     * Add a where not null condition from this field.
     *
     * @param  LaramoreBuilder $builder
     * @param  string          $boolean
     * @return LaramoreBuilder
     */
    public function whereNotNull(LaramoreBuilder $builder, string $boolean='and'): LaramoreBuilder
    {
        return $this->whereNull($builder, $boolean, true);
    }

    /**
     * Add a where in condition from this field.
     *
     * @param  LaramoreBuilder $builder
     * @param  Collection      $value
     * @param  string          $boolean
     * @param  boolean         $notIn
     * @return LaramoreBuilder
     */
    public function whereIn(LaramoreBuilder $builder, Collection $value=null,
                            string $boolean='and', bool $notIn=false): LaramoreBuilder
    {
        return $this->where($builder, Operator::in(), $value->map(function ($subValue) {
            return $this->dry($subValue);
        }), $boolean, $notIn);
    }

    /**
     * Add a where not in condition from this field.
     *
     * @param  LaramoreBuilder $builder
     * @param  Collection      $value
     * @param  string          $boolean
     * @return LaramoreBuilder
     */
    public function whereNotIn(LaramoreBuilder $builder, Collection $value=null, string $boolean='and'): LaramoreBuilder
    {
        return $this->whereIn($builder, $value, $boolean, true);
    }

    /**
     * Add a where condition from this field.
     *
     * @param  LaramoreBuilder $builder
     * @param  OperatorElement $operator
     * @param  mixed           $value
     * @param  string          $boolean
     * @return LaramoreBuilder
     */
    public function where(LaramoreBuilder $builder, OperatorElement $operator,
                          $value=null, string $boolean='and'): LaramoreBuilder
    {
        return $this->getOwner()->whereFieldValue($this, $builder, $operator, $this->dry($value), $boolean);
    }
}
