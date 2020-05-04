<?php
/**
 * Defines all meta data for a specific model.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Eloquent;

use Laramore\Exceptions\LaramoreException;
use Laramore\Contracts\Field\{
    Field, RelationField
};
use Laramore\Fields\ManyToOne;

class PivotMeta extends Meta
{
    /**
     * List of pivot relations.
     *
     * @var array
     */
    protected $pivots = [];

    /**
     * Indicate the this meta is a pivot one.
     *
     * @return boolean
     */
    public function isPivot(): bool
    {
        return true;
    }

    /**
     * Return all foreign pivots.
     *
     * @return array
     */
    public function getPivots(): array
    {
        return $this->pivots;
    }

    /**
     * Lock all owned fields.
     *
     * @return void
     */
    protected function locking()
    {
        if (count($this->pivots) !== 2) {
            throw new LaramoreException($this, 'You need to specify in your pivot __meta function, the two pivot attributes');
        }

        parent::locking();
    }

    /**
     * Define a field with a given name.
     *
     * @param string $name
     * @param Field  $field
     * @return self
     */
    public function setField(string $name, Field $field)
    {
        if (count($this->pivots) !== 2 && ($field instanceof ManyToOne)) {
            $this->pivots[] = $field;
        }

        return parent::setField($name, $field);
    }

    /**
     * Define pivots for this meta pivot.
     *
     * @param RelationField $pivotSource
     * @param RelationField $pivotTarget
     * @return self
     */
    public function pivots(RelationField $pivotSource, RelationField $pivotTarget)
    {
        $this->needsToBeUnlocked();

        $this->pivots = [];

        $this->addPivot($pivotSource);
        $this->addPivot($pivotTarget);

        return $this;
    }

    /**
     * Add a pivot for this pivot meta.
     *
     * @param RelationField $pivot
     * @return void
     */
    protected function addPivot(RelationField $pivot)
    {
        if (is_string($pivot)) {
            $pivot = $this->get($pivot);
        }

        $this->pivots[] = $pivot;
    }
}