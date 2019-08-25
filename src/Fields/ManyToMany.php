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
use Laramore\Facades\MetaManager;
use Laramore\Traits\Field\ManyToManyRelation;
use Laramore\{
    Meta, FakePivot
};

class ManyToMany extends CompositeField
{
    use ManyToManyRelation;

    protected $reversedName;
    protected $usePivot;
    protected $pivotClassName;
    protected $unique = true;

    protected static $defaultFields = [];
    protected static $defaultLinks = [
        'reversed' => Link\BelongsToMany::class,
    ];

    public function on(string $model, string $reversedName=null)
    {
        $this->needsToBeUnlocked();

        $this->defineProperty('on', $this->getLink('reversed')->off = $model);
        $this->to($model::getMeta()->getPrimary()->attname);

        $this->reversedName($reversedName);

        return $this;
    }

    public function to(string $name)
    {
        $this->needsToBeUnlocked();

        $this->defineProperty('to', $this->getLink('reversed')->from = $name);

        return $this;
    }

    public function reversedName(string $reversedName=null)
    {
        $this->needsToBeUnlocked();

        $this->linksName['reversed'] = $reversedName ?: '*{modelname}';

        return $this;
    }

    public function usePivot(string $pivotClassName=null)
    {
        $this->needsToBeUnlocked();

        $this->defineProperty('usePivot', true);
        $this->defineProperty('pivotClassName', $pivotClassName);

        return $this;
    }

    public function unique(bool $unique=true)
    {
        $this->checkOwned();

        $this->defineProperty('unique', $unique);
    }

    protected function loadPivotMeta()
    {
        $offMeta = $this->getMeta();
        $onMeta = $this->on::getMeta();
        $offTable = $offMeta->getTableName();
        $onTable = $onMeta->getTableName();
        $offName = $offMeta->getModelClassName();
        $onName = $onMeta->getModelClassName();

        $pivotClassName = 'App\\Pivots\\'.ucfirst($offName).ucfirst($onName);

        if ($this->usePivot) {
            if ($this->pivotClassName) {
                $pivotClassName = $this->pivotClassName;
            }
        } else {
            \class_alias(FakePivot::class, $pivotClassName);
            $this->setProperty('pivotMeta', $pivotClassName::getMeta());

            $this->pivotMeta->set(
                $offName,
                $offField = Foreign::field()->on($this->getMeta()->getModelClass())->reversedName('pivot'.ucfirst($onTable))
            );

            $this->pivotMeta->set(
                $onName,
                $onField = Foreign::field()->on($this->on)->reversedName('pivot'.ucfirst($offTable))
            );

            $this->pivotMeta->setTableName($offTable.'_'.$onTable);
        }

        [$to, $from] = $this->pivotMeta->getPivots();

        $this->setProperty('pivotTo', $to);
        $this->setProperty('pivotFrom', $from);

        if ($this->unique) {
            $this->pivotMeta->unique($this->pivotTo, $this->pivotFrom);
        }

        MetaManager::addMeta($this->pivotMeta);
    }

    public function owned()
    {
        $this->loadPivotMeta();

        $this->defineProperty('off', $this->getLink('reversed')->on = $this->getMeta()->getModelClass());

        parent::owned();
    }

    protected function locking()
    {
        if (!$this->on) {
            throw new \Exception('Related model settings needed. Set it by calling `on` method');
        }

        $this->defineProperty('reversedName', $this->getLink('reversed')->name);
        $this->defineProperty('from', $this->getLink('reversed')->to = $this->getMeta()->getPrimary()->attname);

        parent::locking();
    }
}
