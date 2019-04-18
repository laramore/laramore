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

use Illuminate\Support\Str;
use Laramore\Fields\{
	Field, Timestamp
};
use Laramore\LinkFields\LinkField;
use Laramore\CompositeFields\CompositeField;
use Laramore\Interfaces\{
	IsAField, IsAPrimaryField
};
use Laramore\Traits\Model\HasLaramore;
use Laramore\Traits\IsPrepared;
use Laramore\Template;

class Meta
{
    use IsPrepared;

    protected $modelClass;
    protected $modelClassName;
    protected $tableName;

    protected $fields;
    protected $composites;
    protected $links;

    protected $hasPrimary = false;
    protected $hasTimestamps = false;
    protected $primary;
    protected $index;
    protected $unique;

    protected $fieldManager;
    protected $locked = false;

    public function __construct($modelClass)
    {
        $this->modelClass = $modelClass;
        $this->modelClassName = (new \ReflectionClass($modelClass))->getShortName();
        $this->tableName = $this->getDefaultTableName();

        $this->fields = config('database.table.fields', []);
        $this->composites = config('database.table.composites', []);
        $this->links = config('database.table.links', []);
        $this->primary = config('database.table.primary');
        $this->index = config('database.table.index', []);
        $this->unique = config('database.table.unique', []);
        $this->defaultFieldConfigs = config('database.fields', []);

        if (config('database.table.timestamps', false)) {
            $this->useTimestamps();
        }

        $this->fieldManager = new FieldManager($this);
    }

    public function getModelClass()
    {
        return $this->modelClass;
    }

    public function getModelClassName()
    {
        return $this->modelClassName;
    }

    public function getDefaultTableName()
    {
        return implode('_', array_map(function ($element) {
            return Str::plural($element);
        }, explode(' ', Str::snake($this->modelClassName, ' '))));
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function setTableName(string $tableName)
    {
        $this->checkLock();

        $this->tableName = $tableName;
    }

    protected function manipulateField($field)
    {
        if ($field instanceof IsAPrimaryField) {
            $this->primary($field);
        }

        return $field;
    }

    public function hasField($name)
    {
        return isset($this->getFields()[$name]);
    }

    public function getField($name)
    {
        if ($this->hasField($name)) {
            return $this->getFields()[$name];
        } else {
            throw new \Exception($name.' field does not exist');
        }
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function hasLink($name)
    {
        return isset($this->getLinks()[$name]);
    }

    public function getLink($name)
    {
        if ($this->hasLink($name)) {
            return $this->getLinks()[$name];
        } else {
            throw new \Exception($name.' link field does not exist');
        }
    }

    public function getLinks()
    {
        return $this->links;
    }

    public function hasComposite($name)
    {
        return isset($this->getComposites()[$name]);
    }

    public function getComposite($name)
    {
        if ($this->hasComposite($name)) {
            return $this->getComposites()[$name];
        } else {
            throw new \Exception($name.' link field does not exist');
        }
    }

    public function getComposites()
    {
        return $this->composites;
    }

    public function has($name)
    {
        return isset($this->allFields()[$name]);
    }

    public function get($name)
    {
        if ($this->has($name)) {
            return $this->allFields()[$name];
        } else {
            throw new \Exception($name.' real or link field does not exist');
        }
    }

    // TODO: Ajouter les conf par défaut pour chaque field s'il n'existe pas => StringField: [length: 256]
    public function set($name, $value)
    {
        if ($this->has($name)) {
            throw new \Exception('It is not allowed to reset the field '.$name);
        }

        if ($value instanceof Field) {
            $value = $this->manipulateField($value)->own($this, $name);
            $this->fields[$value->name] = $value;
        } else if ($value instanceof CompositeField) {
            $value->own($this, $name);

            $this->composites[$value->name] = $this->manipulateField($value);

            foreach ($value->getFields() as $field) {
                if (!$field->isOwned() || $field->getOwner() !== $value) {
                    throw new \Exception('The field '.$name.' must be owned by the composed field '.$value->name);
                }

                $this->fields[$field->name] = $this->manipulateField($field);
            }
        } else if ($value instanceof LinkField) {
            if (!$this->preparing && !$this->prepared) {
                throw new \Exception('You cannot set link fields. You must prepare this meta before the other one via the `__meta` method.');
            }

            if ($value->isOwned()) {
                if ($value->name !== $name) {
                    throw new \Exception('The link field name must be the same than the given one.');
                }
            } else {
                throw new \Exception('The link field must be owned by a child of the oposite meta.');
            }

            $this->links[$name] = $value;
        } else {
            throw new \Exception('To set a specific field, you have to give a Field object/string');
        }
    }

    public function allFields()
    {
        return array_merge(
	        $this->fields,
	        $this->composites,
	        $this->links
        );
    }

    public function getFillableFields()
    {
        $fillable = [];

        foreach ($this->getFields() as $name => $field) {
            if ($field->fillable) {
                $fillable[] = $name;
            }
        }

        return $fillable;
    }

    public function getVisibleFields()
    {
        $visible = [];

        foreach ($this->getFields() as $name => $field) {
            if ($field->visible) {
                $visible[] = $name;
            }
        }

        return $visible;
    }

    protected function preparing()
    {
        foreach ($this->getComposites() as $field) {
            $field->prepare();
        }
    }

    public function lock()
    {
        $this->checkLock();

        foreach ($this->allFields() as $field) {
            $field->lock();
        }

        $this->locked = true;

        return $this;
    }

    public function isLocked()
    {
        return $this->locked;
    }

    public function checkLock()
    {
        if ($this->isLocked()) {
            throw new \Exception('The meta is locked, nothing can change');
        }

        return $this;
    }

    public function getPrimary()
    {
        return $this->primary;
    }

    public function primary(...$fields)
    {
        if ($this->hasPrimary) {
            throw new \Exception('It is not possible de set primary fields after another');
        }

        $this->hasPrimary = true;

        if (count($fields) === 1) {
            $this->primary = $fields[0];
        } else if (count($fields) > 1) {
            $this->primary = $fields;
        }

        return $this;
    }

    public function unique(...$fields)
    {
        $unique = [];

        if (count($fields) > 0) {
            if (count($fields) > 1) {
                foreach ($fields as $field) {
                    if ($field instanceof string) {
                        $unique[] = $this->getField($field);
                    } else if ($field instanceof CompositeField) {
                        if ($this->get($field->name) !== $field) {
                               throw new \Exception('It is not allowed to use external composite fields');
                        } else {
                            foreach ($field->getFields() as $compositeField) {
                                $unique[] = $compositeField;
                            }
                        }
                    } else if ($field instanceof IsAField) {
                        if ($this->get($field->name) !== $field) {
                            throw new \Exception('It is not allowed to use external field');
                        } else {
                            $unique[] = $field;
                        }
                    }
                }

                $this->unique[] = $unique;
            } else {
                $field = $fields[0];

                if ($field instanceof string) {
                    $this->getField($field)->unique();
                } else if ($field instanceof CompositeField) {
                    if ($this->get($field->name) !== $field) {
                        throw new \Exception('It is not allowed to use external composite fields');
                    } else {
                        return $this->unique(...$field->getFields());
                    }
                } else if ($field instanceof IsAField) {
                    if ($this->get($field->name) !== $field) {
                        throw new \Exception('It is not allowed to use external field');
                    } else {
                        $field->unique();
                    }
                }
            }
        }

        return $this;
    }

    public function useTimestamps()
    {
        try {
            $this->set(
            ($this->modelClass::CREATED_AT ?? 'created_at'),
            Timestamp::field(Field::NOT_NULLABLE | Field::VISIBLE)->useCurrent()
            );

            $this->set(
            ($this->modelClass::UPDATED_AT ?? 'updated_at'),
            Timestamp::field(Field::NULLABLE | Field::VISIBLE)->useCurrent()
            );
        } catch (\Exception $e) {
            throw new \Exception('Can not set timestamps. Maybe already set ?');
        }

        $this->hasTimestamps = true;

        return $this;
    }

    public function timestamps()
    {
        return $this->useTimestamps();
    }

    public function hasTimestamps()
    {
        return $this->hasTimestamps;
    }

    public function __get($name)
    {
        if ($name === 'fields') {
            return $this->fieldManager;
        }
    }

    public function __set($name, $value)
    {
        if ($name === 'tableName') {
            return $this->setTableName($value);
        }
    }
}
