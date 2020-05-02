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
use Laramore\Contracts\Field\Field;
use Laramore\Fields\ManyToOne;

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

        if (!($pivot instanceof ManyToOne)) {
            throw new LaramoreException($this, 'The pivots need to be foreign fields.');
        }

        $this->pivots[] = $pivot;
    }
}
