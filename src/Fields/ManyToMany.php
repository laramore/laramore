<?php
/**
 * Define a may to many field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

use Illuminate\Support\Str;
use Laramore\Facades\Option;
use Laramore\Contracts\Eloquent\LaramoreModel;
use Laramore\Contracts\Field\{
    Field, RelationField, ManyRelationField, Constraint\Constraint
};
use Laramore\Traits\Field\ManyToManyRelation;

class ManyToMany extends BaseComposed implements ManyRelationField
{
    use ManyToManyRelation;

    /**
     * Model the relation is on.
     *
     * @var string
     */
    protected $targetModel;

    /**
     * Pivot meta name.
     *
     * @var \Larmore\Contracts\Eloquent\LaramoreMeta
     */
    protected $pivotMeta;

    /**
     * Pivot source field.
     *
     * @var RelationField
     */
    protected $pivotSource;

    /**
     * Pivot target field.
     *
     * @var RelationField
     */
    protected $pivotTarget;

    /**
     * Indicate if this use a specific pivot.
     *
     * @var boolean
     */
    protected $usePivot;

    /**
     * Pivot class name.
     *
     * @var string
     */
    protected $pivotClass;

    /**
     * Pivot namespace.
     *
     * @var string
     */
    protected $pivotNamespace;

    /**
     * Unique relation.
     *
     * @var bool
     */
    protected $uniqueRelation = false;

    /**
     * Define the pivot and reversed pivot names.
     *
     * @param string $pivotName
     * @param string $reversedPivotName
     * @return self
     */
    public function pivotName(string $pivotName, string $reversedPivotName=null)
    {
        $this->needsToBeUnlocked();

        $this->templates['pivot'] = $pivotName;

        if (!\is_null($reversedPivotName)) {
            $this->reversedPivotName($reversedPivotName);
        }

        return $this;
    }

    /**
     * Define the reversed pivot name.
     *
     * @param string $reversedPivotName
     * @return self
     */
    public function reversedPivotName(string $reversedPivotName=null)
    {
        $this->needsToBeUnlocked();

        $this->templates['reversed_pivot'] = $reversedPivotName;

        return $this;
    }

    /**
     * Return the reversed field.
     *
     * @return RelationField
     */
    public function getReversedField(): RelationField
    {
        return $this->getField('reversed');
    }

    /**
     * Define the model on which to point.
     *
     * @param string $model
     * @param string $reversedName
     * @return self
     */
    public function on(string $model, string $reversedName=null)
    {
        $this->needsToBeUnlocked();

        $this->defineProperty('targetModel', $model);

        if ($model === 'self') {
            $this->addOption(Option::nullable());
        } else {
            $this->getField('reversed')->setMeta($model::getMeta());
        }

        if ($reversedName) {
            $this->reversedName($reversedName);
        }

        return $this;
    }

    /**
     * Define on self.
     *
     * @return self
     */
    public function onSelf()
    {
        return $this->on('self');
    }

    /**
     * Indicate if it is a relation on itself.
     *
     * @return boolean
     */
    public function isOnSelf()
    {
        $model = $this->getTargetModel();

        return $model === $this->getMeta()->getModelClass() || $model === 'self';
    }

    /**
     * Define the reversed name of the relation.
     *
     * @param string $reversedName
     * @return self
     */
    public function reversedName(string $reversedName)
    {
        $this->needsToBeUnlocked();

        $this->templates['reversed'] = $reversedName;

        return $this;
    }

    /**
     * Indicate which pivot to use.
     *
     * @param string $pivotClass
     * @return self
     */
    public function usePivot(string $pivotClass=null)
    {
        $this->needsToBeUnlocked();

        $this->defineProperty('usePivot', true);
        $this->defineProperty('pivotClass', $pivotClass);

        return $this;
    }

    /**
     * Load the pivot meta.
     *
     * @return void
     */
    protected function loadPivotMeta()
    {
        $offMeta = $this->getMeta();
        $offName = Str::snake($offMeta->getModelClassName());
        $onName = Str::snake(Str::singular($this->getName()));
        $namespaceName = $this->pivotNamespace;
        $pivotClassName = ucfirst($offName).ucfirst($onName);
        $pivotClass = "$namespaceName\\$pivotClassName";

        if ($this->usePivot) {
            if ($this->pivotClass) {
                $pivotClass = $this->pivotClass;
            }

            $this->setProperty('pivotMeta', $pivotClass::getMeta());
        } else {
            $this->pivotName = $this->replaceInFieldTemplate($this->templates['pivot'], $offName);
            $this->reversedPivotName = $this->replaceInFieldTemplate($this->templates['reversed_pivot'], $onName);

            // Create dynamically the pivot class (only and first time I use eval, really).
            if (!\class_exists($pivotClass)) {
                eval("namespace $namespaceName; class $pivotClassName extends \Laramore\Eloquent\FakePivot {}");
            }

            $this->setProperty('pivotMeta', $pivotClass::getMeta());

            $this->pivotMeta->setField(
                $offName,
                $offField = ManyToOne::field()->on($this->getMeta()->getModelClass())
            );

            $onField = ManyToOne::field()->on($this->getTargetModel());

            if ($this->isOnSelf()) {
                $onField->reversedName($this->templates['self_reversed_pivot']);
            }

            $this->pivotMeta->setField(
                $onName,
                $onField
            );

            $this->reversedPivotName = $onField->getReversedField()->getName();

            $this->pivotMeta->pivots($onField, $offField);
        }

        [$source, $target] = $this->pivotMeta->getPivots();

        $this->setProperty('pivotSource', $source);
        $this->setProperty('pivotTarget', $target);

        if ($this->uniqueRelation) {
            $this->unique($this->uniqueRelation === true ? null : $this->uniqueRelation);
        }
    }

    /**
     * Define on and off variables after being owned.
     *
     * @return void
     */
    protected function owned()
    {
        if ($this->getTargetModel() === 'self') {
            $this->on($this->getSourceModel());
        }

        if (\is_null($this->pivotMeta)) {
            $this->loadPivotMeta();
        }

        $this->pivotName = $this->replaceInFieldTemplate($this->templates['pivot'], $this->getName());

        parent::owned();

        $this->getReversedField()->pivotName(
            $this->replaceInFieldTemplate($this->templates['reversed_pivot'], $this->getReversedField()->getName())
        );
    }

    /**
     * Define a unique constraint.
     *
     * @param  string $name
     * @return self
     */
    public function unique(string $name=null)
    {
        $this->needsToBeUnlocked();

        if (\is_null($this->pivotMeta)) {
            $this->uniqueRelation = $name ?: true;
        } else {
            $this->uniqueRelation = true;
            $this->pivotMeta->unique([$this->getPivotSource(), $this->getPivotTarget()], $name);
        }

        return $this;
    }

    /**
     * Indicate if the relation is head on or not.
     * Usefull to know which to use between source and target.
     *
     * @return boolean
     */
    public function isRelationHeadOn(): bool
    {
        return true;
    }

    /**
     * Model where the relation is set from.
     *
     * @return string
     */
    public function getSourceModel(): string
    {
        $this->needsToBeOwned();

        return $this->getMeta()->getModelClass();
    }

    /**
     * Model where the relation is set to.
     *
     * @return string
     */
    public function getTargetModel(): string
    {
        $this->needsToBeOwned();

        return $this->targetModel;
    }

    /**
     * Return the source of the relation.
     *
     * @return Constraint
     */
    public function getSource(): Constraint
    {
        $this->needsToBeOwned();

        return $this->getSourceModel()::getMeta()
            ->getConstraintHandler()->getPrimary();
    }

    /**
     * Return the target of the relation.
     *
     * @return Constraint
     */
    public function getTarget(): Constraint
    {
        $this->needsToBeOwned();

        return $this->getTargetModel()::getMeta()
            ->getConstraintHandler()->getPrimary();
    }

    /**
     * Return the set value for a specific field.
     *
     * @param Field                            $field
     * @param LaramoreModel|array|\ArrayAccess $model
     * @param mixed                            $value
     * @return mixed
     */
    public function setFieldValue(Field $field, $model, $value)
    {
        if ($field->get($model) !== $value) {
            $this->reset($model);
        }

        return parent::setFieldValue($field, $model, $value);
    }
}
