<?php

namespace Laramore\Fields;

use Illuminate\Support\Str;
use Laramore\Meta;

abstract class CompositeField extends BaseField
{
    protected $fields = [];
    protected $links = [];
    protected $fieldsName = [];
    protected $linksName = [];
    protected $uniques = [];

    protected static $defaultFields = [];
    protected static $defaultLinks = [];
    protected static $defaultFieldNameTemplate = '${name}_${fieldname}';
    protected static $defaultLinkNameTemplate = '*{modelname}';

    protected function __construct(array $fields=null, array $links=null)
    {
        foreach ($fields ?: static::$defaultFields as $key => $field) {
            if (!is_string($key)) {
                throw new \Exception('The composite fields need names');
            }

            $this->fields[$key] = $this->generateField($field);
        }

        foreach ($links ?: static::$defaultLinks as $key => $link) {
            if (!is_string($key)) {
                throw new \Exception('The composite fields need names');
            }

            $this->links[$key] = $this->generateLink($link);
        }
    }

    /**
     * Call the constructor and generate the composite field.
     *
     * @return static
     */
    public static function field()
    {
        return new static();
    }

    public static function composite(array $fields=null, array $links=null)
    {
        return new static($fields, $links);
    }

    protected function generateField($field): Field
    {
        if (is_array($field)) {
            if (is_array($field[1])) {
                return $field[0]::field(...$field[1]);
            } else {
                return $field[0]::field($field[1]);
            }
        } else if (is_string($field)) {
            return $field::field();
        } else {
            return $field;
        }
    }

    protected function generateLink($link): LinkField
    {
        if (is_array($link)) {
            if (is_array($link[1])) {
                return $link[0]::link(...$link[1]);
            } else {
                return $link[0]::link($link[1]);
            }
        } else if (is_string($link)) {
            return $link::link();
        } else {
            return $link;
        }
    }

    public function getDefaultProperties(): array
    {
        return [
            'type' => 'composite',
        ];
    }

    public static function setDefaultFields(array $defaultFields)
    {
        foreach ($defaultFields as $key => $defaultField) {
            static::setDefaultField($key, $defaultFields);
        }

        return static::class;
    }

    public static function setDefaultField(string $key, string $field)
    {
        if ($field instanceof Field) {
            if (isset(static::$defaultField[$key])) {
                static::$defaultField[$key] = $field;
            } else {
                throw new \Exception('The default field key does not exist');
            }
        } else {
            throw new \Exception('Need a field class name');
        }

        return static::class;
    }

    public static function setDefaultLinks(array $defaultLinks)
    {
        foreach ($defaultLinks as $key => $defaultLink) {
            static::setDefaultField($key, $defaultLinks);
        }

        return static::class;
    }

    public static function setDefaultLink(string $key, string $link)
    {
        if ($link instanceof Link) {
            if (isset(static::$defaultLink[$key])) {
                static::$defaultLink[$key] = $link;
            } else {
                throw new \Exception('The default link key does not exist');
            }
        } else {
            throw new \Exception('Need a link class name');
        }

        return static::class;
    }

    public static function setDefaultFieldNameTemplate(array $defaultFieldNameTemplate)
    {
        static::$defaultFieldNameTemplate = $defaultFieldNameTemplate;

        return static::class;
    }

    public static function setDefaultLinkNameTemplate(array $defaultLinkNameTemplate)
    {
        static::$defaultLinkNameTemplate = $defaultLinkNameTemplate;

        return static::class;
    }

    public function replaceInTemplate(string $template, array $keyValues)
    {
        foreach ($keyValues as $varName => $value) {
            $template = str_replace('*{'.$varName.'}', Str::plural($value),
                str_replace('^{'.$varName.'}', \ucwords($value),
                    str_replace('${'.$varName.'}', $value, $template)
                )
            );
        }

        return $template;
    }

    public function unique()
    {
        $this->checkLock();

        $this->unique[] = $this->getFields();

        return $this;
    }

    public function owning()
    {
        $this->ownFields();
        $this->ownLinks();
    }

    protected function ownFields()
    {
        $keyValues = [
            'modelname' => strtolower($this->getOwner()->getModelClassName()),
            'name' => $this->name,
        ];

        foreach ($this->fields as $fieldname => $field) {
            $keyValues['fieldname'] = $fieldname;
            $name = $this->replaceInTemplate(($this->fieldsName[$fieldname] ?? static::$defaultFieldNameTemplate), $keyValues);
            $this->fields[$fieldname] = $field->own($this, $name);
        }
    }

    protected function ownLinks()
    {
        $keyValues = [
            'modelname' => strtolower($this->getOwner()->getModelClassName()),
            'name' => $this->name,
        ];

        foreach ($this->links as $linkname => $link) {
            $keyValues['linkname'] = $linkname;
            $name = $this->replaceInTemplate(($this->linksName[$linkname] ?? static::$defaultFieldNameTemplate), $keyValues);
            $this->links[$linkname] = $link->own($this, $name);
        }
    }

    protected function locking()
    {
        $this->lockFields();
        $this->lockLinks();

        if ((count($this->fields) + count($this->links)) === 0) {
            throw new \Exception('A composite field needs at least one field or link');
        }

        return $this;
    }

    protected function lockFields()
    {
        foreach ($this->fields as $field) {
            $field->lock();
        }
    }

    protected function lockLinks()
    {
        foreach ($this->links as $link) {
            $link->lock();
        }
    }

    public function getFields()
    {
        return array_values($this->fields);
    }

    public function getUnique()
    {
        return $this->unique;
    }

    public function castValue($value)
    {
        return $this->castValue($value);
    }

    public function getValue($mode, $value)
    {
        return $value;
    }

    public function setValue($mode, $value)
    {
        $value = $this->castValue($value);

        $model->setAttribute($this->name, $value);
    }

    public function relationValue($model)
    {
        return $this;
    }

    public function whereValue($model, ...$args)
    {
        return $model->where($this->name, ...$args);
    }
}
