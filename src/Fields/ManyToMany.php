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
use Laramore\Meta;

class ManyToMany extends CompositeField
{
    protected $on;
    protected $to;
    protected $off;
    protected $from;
    protected $pivotMeta;
    protected $pivotTo;
    protected $pivotFrom;
    protected $reversedName;
    protected $unique = true;

    protected static $defaultFields = [];
    protected static $defaultLinks = [
        'reversed' => Link\BelongsToMany::class,
    ];

    public function on(string $model, string $reversedName=null)
    {
        $this->checkLock();

        $this->defineProperty('on', $this->getLink('reversed')->off = $model);
        $this->to($model::getMeta()->getPrimary()->attname);

        $this->reversedName($reversedName);

        return $this;
    }

    public function to(string $name)
    {
        $this->checkLock();

        $this->defineProperty('to', $this->getLink('reversed')->from = $name);

        return $this;
    }

    public function reversedName(string $reversedName=null)
    {
        $this->linksName['reversed'] = $reversedName ?: '*{modelname}';

        return $this;
    }

    public function unique(bool $unique=true)
    {
        $this->checkOwned();

        $this->defineProperty('unique', $unique);
    }

    protected function createPivotMeta()
    {
        $offMeta = $this->getMeta();
        $onMeta = $this->on::getMeta();
        $offTable = $offMeta->getTableName();
        $onTable = $onMeta->getTableName();
        $offName = $offMeta->getModelClassName();
        $onName = $onMeta->getModelClassName();

        $this->setProperty('pivotMeta', new Meta('App\\Pivots\\'.ucfirst($offName).ucfirst($onName)));
        $this->pivotMeta->set(
            $offName,
            $offField = Foreign::field()->on($this->getModelClass())->reversedName('pivot'.ucfirst($onTable))
        );
        $this->pivotMeta->set(
            $onName,
            $onField = Foreign::field()->on($this->on)->reversedName('pivot'.ucfirst($offTable))
        );
        $this->pivotMeta->tableName = $offTable.'_'.$onTable;

        $this->setProperty('pivotTo', $onField->from);
        $this->setProperty('pivotFrom', $offField->from);

        if ($this->unique) {
            $this->pivotMeta->unique($this->pivotTo, $this->pivotFrom);
        }

        MetaManager::addMeta($this->pivotMeta);
    }

    public function owning()
    {
        $this->createPivotMeta();

        $this->defineProperty('off', $this->getLink('reversed')->on = $this->getModelClass());

        parent::owning();
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

    public function castValue($model, $value)
    {
        if (is_null($value) || $value instanceof $this->on) {
            return $value;
        } else {
            $model = new $this->on;
            $model->setAttribute($this->to, $value, true);

            return $model;
        }
    }

    public function getValue($model, $value)
    {
        return $this->relationValue($model)->get();
    }

    public function setValue($model, $value)
    {
        return $this->castValue($model, $value);
    }

    public function relationValue($model)
    {
        return $model->belongsToMany($this->on, $this->pivotMeta->getTableName(), $this->pivotTo, $this->pivotFrom);
    }

    public function whereValue($query, ...$args)
    {
        if (count($args) > 1) {
            [$operator, $value] = $args;
        } else {
            $operator = '=';
            $value = $args[0] ?? null;
        }

        if (is_object($value)) {
            $value = $value->{$this->on};
        } else if (!is_null($value)) {
            $value = (integer) $value;
        }

        return $query->where($this->from, $operator, $value);
    }

    public function setFieldValue($model, $field, $value)
    {
        return $field->setValue($model, $value);
    }

    public function getFieldValue($model, $field, $value)
    {
        return $field->getValue($model, $value);
    }
}
