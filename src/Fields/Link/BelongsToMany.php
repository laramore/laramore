<?php
/**
 * Define a reverse manytomany field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields\Link;

use Laramore\Traits\Field\ManyToManyRelation;
use Laramore\Fields\CompositeField;

class BelongsToMany extends LinkField
{
    use ManyToManyRelation;

    public function getReversed(): CompositeField
    {
        return $this->getOwner();
    }

    protected function owned()
    {
        parent::owned();

        $this->defineProperty('pivotMeta', $this->getOwner()->pivotMeta);
        $this->defineProperty('pivotTo', $this->getOwner()->pivotTo);
        $this->defineProperty('pivotFrom', $this->getOwner()->pivotFrom);
    }
}
