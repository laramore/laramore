<?php
/**
 * Define a reverse one to one field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields\Reversed;

use Illuminate\Support\Collection;
use Laramore\Elements\OperatorElement;
use Laramore\Fields\BaseField;
use Laramore\Contracts\{
    Eloquent\LaramoreModel, Eloquent\LaramoreBuilder
};
use Laramore\Contracts\Field\RelationField;
use Laramore\Traits\Field\HasOneRelation;

class HasOne extends BaseField implements RelationField
{
    use HasOneRelation;

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
        return $this->getTarget()->getAttribute()
            ->addBuilderOperation($builder, 'whereIn', $value, $boolean, $notIn);
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
        return $this->getTarget()->getAttribute()
            ->addBuilderOperation($builder, 'where', $operator, $value, $boolean);
    }

    /**
     * Return the relation with this field.
     *
     * @param  LaramoreModel $model
     * @return mixed
     */
    public function relate(LaramoreModel $model)
    {
        $relation = $model->hasOne(
            $this->getTargetModel(),
            $this->getTarget()->getAttribute()->getNative(),
            $this->getSource()->getAttribute()->getNative()
        );

        if ($this->hasProperty('when')) {
            return (\call_user_func($this->when, $relation, $model) ?? $relation);
        }

        return $relation;
    }

    /**
     * Reverbate the relation into database or other fields.
     * It should be called by the set method.
     *
     * @param  LaramoreModel $model
     * @return boolean
     */
    public function reverbate(LaramoreModel $model)
    {
        $value = $this->get($model);

        if (!\is_null($value)) {
            $this->getField('id')->set(
                $model,
                \is_null($value) ? null : $this->getTarget()->getAttribute()->get($value)
            );
        }

        if (!$model->exists) {
            return false;
        }

        $modelClass = $this->getSourceModel();
        $primary = $this->getSource()->getAttribute();
        $id = $model->getKey();
        $valueId = $value[$primary->getName()];

        $primary->addBuilderOperation(
            $modelClass::where($this->to, $id),
            'where',
            $valueId
        )->update([$this->to => null]);

        $primary->addBuilderOperation(
            (new $modelClass)->newQuery(),
            'where',
            $valueId
        )->update([$this->to => $id]);

        return true;
    }
}
