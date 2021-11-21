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

use Illuminate\Support\Str;
use Laramore\Exceptions\LaramoreException;
use Laramore\Contracts\{
    Eloquent\LaramorePivotMeta, Field\Field, Field\RelationField
};
use Laramore\Fields\ManyToOne;

class PivotMeta extends Meta implements LaramorePivotMeta
{
    /**
     * Return the default table name for this meta.
     *
     * @return string
     */
    public function getDefaultTableName(): string
    {
        return \implode('_', \array_map(function ($element) {
            return Str::plural($element);
        }, \array_merge(
            \is_null($this->getModelGroup()) ? [] : \explode('_', $this->getModelGroup()),
            ['pivot'],
            \explode('_', $this->modelName),
        )));
    }

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
        if (count($this->pivots) != 2) {
            throw new LaramoreException('You need to specify in your pivot __meta function, the two pivot attributes');
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
        if (count($this->pivots) != 2 && ($field instanceof ManyToOne)) {
            $this->pivots[] = $field;
        }

        return parent::setField($name, $field);
    }

    /**
     * Add a pivot for this pivot meta.
     *
     * @param RelationField $pivot
     * @return self
     */
    protected function addPivot(RelationField $pivot)
    {
        if (is_string($pivot)) {
            $pivot = $this->get($pivot);
        }

        $this->pivots[] = $pivot;

        return $this;
    }
}
