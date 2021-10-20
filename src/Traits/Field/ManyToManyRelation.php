<?php
/**
 * Add multiple methods for many to many relations.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Traits\Field;

use Illuminate\Support\Collection;
use Laramore\Elements\OperatorElement;
use Laramore\Facades\Operator;
use Laramore\Contracts\{
    Field\AttributeField, Eloquent\LaramoreModel, Eloquent\LaramoreBuilder, Eloquent\LaramoreCollection
};
use Laramore\Facades\Option;

trait ManyToManyRelation
{
    use ModelRelation;

    /**
     * Pivot name.
     *
     * @var string
     */
    protected $pivotName;

    /**
     * Return the reversed pivot name.
     *
     * @return string
     */
    public function getReversedPivotName()
    {
        return $this->getReversedField()->getPivotName();
    }

    /**
     * Required option is not available for M2M relations.
     *
     * @return void
     */
    protected function checkOptions()
    {
        parent::checkOptions();

        $name = $this->getQualifiedName();

        if ($this->hasOption(Option::required())) {
            throw new \LogicException("The field `$name` cannot be required as it is a m2m relation");
        }
    }

    /**
     * Cast the value in the correct format.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function cast($value)
    {
        if ($value instanceof Collection) {
            return $value;
        }

        if (\is_null($value) || \is_array($value)) {
            return collect($value);
        }

        return collect($this->castModel($value));
    }

    /**
     * Cast the value to be used as a correct model.
     *
     * @param  mixed $value
     * @return LaramoreModel
     */
    public function castModel($value)
    {
        $modelClass = $this->getTargetModel();

        if ($value instanceof $modelClass) {
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
     * Reverbate the relation into database or other fields.
     * It should be called by the set method.
     *
     * @param  LaramoreModel $model
     * @param  mixed         $value
     * @return mixed
     */
    public function reverbate(LaramoreModel $model, $value)
    {
        if ($model->exists) {
            $this->relate($model)->sync($value);
        }

        return $value;
    }

    /**
     * Return all pivot attributes.
     *
     * @return array<AttributeField>
     */
    public function getPivotAttributes(): array
    {
        $fields = \array_values($this->getPivotMeta()->getFields(AttributeField::class));

        $fields = \array_filter($fields, function (AttributeField $field) {
            return $field->visible;
        });

        return \array_map(function (AttributeField $field) {
            return $field->getNative();
        }, $fields);
    }

    /**
     * Return the relation with this field.
     *
     * @param  LaramoreModel $model
     * @return mixed
     */
    public function relate(LaramoreModel $model)
    {
        $relation = $model->belongsToMany(
            $this->getTargetModel(),
            $this->getPivotMeta()->getTableName(),
            $this->getPivotTarget()->getSource()->getAttribute()->getNative(),
            $this->getPivotSource()->getSource()->getAttribute()->getNative(),
            $this->getTarget()->getAttribute()->getNative(),
            $this->getSource()->getAttribute()->getNative(),
            $this->getName()
        )->withPivot($this->getPivotAttributes())
            ->using($this->getPivotMeta()->getModelClass())
            ->as($this->getPivotName());

        if ($this->hasProperty('when')) {
            return (\call_user_func($this->when, $relation, $model) ?? $relation);
        }

        return $relation;
    }

    /**
     * Add a where null condition from this field.
     *
     * @param  LaramoreBuilder $builder
     * @param  string          $boolean
     * @param  boolean         $notIn
     * @param  \Closure|null   $callback
     * @return LaramoreBuilder
     */
    public function whereNull(LaramoreBuilder $builder, string $boolean='and',
                              bool $notIn=false, \Closure $callback=null): LaramoreBuilder
    {
        if ($notIn) {
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
     * @param  \Closure|null   $callback
     * @return LaramoreBuilder
     */
    public function whereNotNull(LaramoreBuilder $builder, string $boolean='and', $operator=null,
                                 int $count=1, \Closure $callback=null): LaramoreBuilder
    {
        return $builder->has($this->getName(), (string) ($operator ?? Operator::supOrEq()), $count, $boolean, $callback);
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
        $attribute = $this->getSource()->getAttribute();

        return $this->whereNull($builder, $boolean, $notIn, function ($subBuilder) use ($attribute, $value) {
            return $attribute->whereIn($subBuilder, $value);
        });
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
     * @param  integer         $count
     * @return LaramoreBuilder
     */
    public function where(LaramoreBuilder $builder, OperatorElement $operator, $value=null,
                          string $boolean='and', int $count=null): LaramoreBuilder
    {
        $attname = $this->getTarget()->getAttribute()->getNative();

        return $this->whereNotNull($builder, $boolean, $operator, ($count ?? \count($value)),
            function ($query) use ($attname, $value) {
                return $query->whereIn($attname, $value);
                // foreach ($value as $subValue) {
                //     $query = $query->where($attname, $subValue);
                // }

                // return $query;
            }
        );
    }

    /**
     * Attach value to the model relation.
     *
     * @param LaramoreModel $model
     * @param mixed         $value
     * @return LaramoreModel
     */
    public function attach(LaramoreModel $model, $value)
    {
        $this->relate($model)->attach($value);

        $model->unsetRelation($this->getName());

        return $model;
    }

    /**
     * Detach value from the model relation.
     *
     * @param LaramoreModel|array|\Illuminate\Contracts\Support\\ArrayAccess $model
     * @param mixed         $value
     * @return LaramoreModel
     */
    public function detach($model, $value)
    {
        $this->relate($model)->detach($value);

        $model->unsetRelation($this->getName());

        return $model;
    }

    /**
     * Sync value with the model relation.
     *
     * @param LaramoreModel|array|\Illuminate\Contracts\Support\\ArrayAccess $model
     * @param mixed         $value
     * @return LaramoreModel
     */
    public function sync($model, $value)
    {
        $this->set($model, $value);

        $model->unsetRelation($this->getName());

        return $model;
    }

    /**
     * Toggle value to the model relation.
     *
     * @param LaramoreModel|array|\Illuminate\Contracts\Support\\ArrayAccess $model
     * @param mixed         $value
     * @return LaramoreModel
     */
    public function toggle($model, $value)
    {
        $this->relate($model)->toggle($value);

        $model->unsetRelation($this->getName());

        return $model;
    }

    /**
     * Sync without detaching value from the model relation.
     *
     * @param LaramoreModel|array|\Illuminate\Contracts\Support\\ArrayAccess $model
     * @param mixed         $value
     * @return LaramoreModel
     */
    public function syncWithoutDetaching($model, $value)
    {
        $this->relate($model)->syncWithoutDetaching($value);

        $model->unsetRelation($this->getName());

        return $model;
    }

    /**
     * Update existing pivot for the value with the model relation.
     *
     * @param LaramoreModel|array|\Illuminate\Contracts\Support\\ArrayAccess $model
     * @param mixed         $value
     * @return LaramoreModel
     */
    public function updateExistingPivot($model, $value)
    {
        $this->relate($model)->updateExistingPivot($value);

        $model->unsetRelation($this->getName());

        return $model;
    }
}
