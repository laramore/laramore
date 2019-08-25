<?php
/**
 * Defines all meta data for a specific model.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore;

use Laramore\Exceptions\LaramoreException;
use Laramore\Fields\{
    BaseField, Foreign
};

class PivotMeta extends Meta
{
    protected $pivots = [];

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
     * Manipulate a field as primary ones.
     *
     * @param  BaseField $field
     * @return BaseField
     */
    protected function manipulateField(BaseField $field): BaseField
    {
        if (count($this->pivots) !== 2 && ($field instanceof Foreign)) {
            $this->pivots[] = $field;
        }

        return parent::manipulateField($field);
    }

    public function pivots($pivot1, $pivot2)
    {
        $this->needsToBeUnlocked();

        $this->pivots = [];

        $this->addPivot($pivot1);
        $this->addPivot($pivot2);
    }

    protected function addPivot($pivot)
    {
        if (is_string($pivot)) {
            $pivot = $this->get($pivot);
        }

        if (!($pivot instanceof Foreign)) {
            throw new LaramoreException($this, 'The pivots need to be foreign fields.');
        }

        $this->pivots[] = $pivot;
    }

    public function setModelClassName(string $modelClassName)
    {
        $this->needsToBeUnlocked();

        $this->modelClassName = $modelClassName;
    }
}
