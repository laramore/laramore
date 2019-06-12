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

class BelongsToMany extends LinkField
{
    use ManyToManyRelation;

    protected function owned()
    {
        parent::owned();

        if (is_null($this->off)) {
            throw new \Exception('You need to specify `off`');
        }

        $this->defineProperty('pivotMeta', $this->getOwner()->pivotMeta);
        $this->defineProperty('pivotTo', $this->getOwner()->pivotTo);
        $this->defineProperty('pivotFrom', $this->getOwner()->pivotFrom);

        $this->off::getMeta()->set($this->name, $this);
    }
}
