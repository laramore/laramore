<?php
/**
 * Define a link field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

use Laramore\Meta;
use Laramore\Interfaces\IsARelationField;
use Laramore\Fields\{
    BaseField, CompositeField
};

abstract class LinkField extends BaseField implements IsARelationField
{
    // Default rules for this type of field.
    public const DEFAULT_LINK = (self::DEFAULT_FIELD ^ self::REQUIRED);

    protected static $defaultRules = self::DEFAULT_LINK;

    protected function setOwner($owner)
    {
        if (is_null($this->off)) {
            throw new \Exception('You need to specify `off`');
        }

        $this->setMeta($this->off::getMeta());

        parent::setOwner($owner);
    }

    protected function owned()
    {
        parent::owned();

        $this->getMeta()->set($this->name, $this);

    }

    protected function checkRules()
    {
        parent::checkRules();

        if ($this->hasProperty('attname')) {
            throw new \Exception('The attribute name property cannot be set for a link field');
        }
    }
}
