<?php
/**
 * Define a composite field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

use Illuminate\Support\Str;
use Laramore\Fields\Link\LinkField;
use Laramore\Interfaces\IsAFieldOwner;
use Laramore\Meta;

abstract class CompositeField extends BaseField implements IsAFieldOwner
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
            return array_shift($field)::field(...$field);
        } else if (is_string($field)) {
            return $field::field();
        } else {
            return $field;
        }
    }

    protected function generateLink($link): LinkField
    {
        if (is_array($link)) {
            return array_shift($link)::field(...$link);
        } else if (is_string($link)) {
            return $link::link();
        } else {
            return $link;
        }
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

    public function hasField(string $name)
    {
        return isset($this->getFields()[$name]);
    }

    public function getField(string $name)
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

    public function hasLink(string $name)
    {
        return isset($this->getLinks()[$name]);
    }

    public function getLink(string $name)
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

    public function has(string $name)
    {
        return isset($this->allFields()[$name]);
    }

    public function get(string $name)
    {
        if ($this->has($name)) {
            return $this->allFields()[$name];
        } else {
            throw new \Exception($name.' real or link field does not exist');
        }
    }

    public function allFields()
    {
        return array_merge(
	        $this->fields,
	        $this->links
        );
    }

    public function getPropagatedPropertyKeys(): array
    {
        return [
            'nullable', 'default'
        ];
    }

    protected function defineProperty(string $key, $value)
    {
        if (in_array($key, $this->getPropagatedPropertyKeys())) {
            foreach ($this->getFields() as $field) {
                $field->setProperty($key, $value);
            }
        }

        return parent::defineProperty($key, $value);
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
        $this->needsToBeUnlocked();

        $this->unique[] = $this->getFields();

        return $this;
    }

    public function owned()
    {
        $this->ownFields();
        $this->ownLinks();
    }

    protected function ownFields()
    {
        $keyValues = [
            'modelname' => strtolower($this->getMeta()->getModelClassName()),
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
            'modelname' => strtolower($this->getMeta()->getModelClassName()),
            'name' => $this->name,
        ];

        foreach ($this->links as $linkname => $link) {
            $keyValues['linkname'] = $linkname;
            $name = $this->replaceInTemplate(($this->linksName[$linkname] ?? static::$defaultLinkNameTemplate), $keyValues);
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

        return parent::locking();
    }

    abstract protected function checkRules();

    abstract protected function setValidations();

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

    public function getUnique()
    {
        return $this->unique;
    }

    /**
     * Return the get value for a specific field.
     *
     * @param Model     $model
     * @param BaseField $field
     * @param mixed     $value
     * @return mixed
     */
    public function getModelAttribute(Model $model, BaseField $field)
    {
        return $this->getOwner()->getModelAttribute($model, $field);
    }

    /**
     * Return the set value for a specific field.
     *
     * @param Model     $model
     * @param BaseField $field
     * @param mixed     $value
     * @return mixed
     */
    public function setModelAttribute(Model $model, BaseField $field, $value)
    {
        return $this->getOwner()->setModelAttribute($model, $field, $value);
    }

    /**
     * Return the set value for a specific field.
     *
     * @param Model     $model
     * @param BaseField $field
     * @param mixed     $value
     * @return mixed
     */
    public function resetModelAttribute(Model $model, BaseField $field)
    {
        return $this->getOwner()->resetModelAttribute($model, $field);
    }

    public function relateModelAttribute(Model $model, BaseField $field)
    {
        return $field->where($model, $model->{$this->attname});
    }

    /**
     * Return the set value for a specific field.
     *
     * @param BaseField $field
     * @param mixed     $value
     * @return mixed
     */
    public function transformFieldAttribute(BaseField $field, $value)
    {
        return $this->getOwner()->transformFieldAttribute($field, $value);
    }

    /**
     * Return the set value for a specific field.
     *
     * @param BaseField $field
     * @param mixed     $value
     * @return mixed
     */
    public function checkFieldAttribute(BaseField $field, $value)
    {
        return $this->getOwner()->checkFieldAttribute($field, $value);
    }

    /**
     * Return the set value for a specific field.
     *
     * @param BaseField $field
     * @param mixed     $value
     * @return mixed
     */
    public function dryFieldAttribute(BaseField $field, $value)
    {
        return $this->getOwner()->dryFieldAttribute($field, $value);
    }

    /**
     * Return the set value for a specific field.
     *
     * @param BaseField $field
     * @param mixed     $value
     * @return mixed
     */
    public function castFieldAttribute(BaseField $field, $value)
    {
        return $this->getOwner()->castFieldAttribute($field, $value);
    }

    /**
     * Return the set value for a specific field.
     *
     * @param BaseField $field
     * @return mixed
     */
    public function defaultFieldAttribute(BaseField $field)
    {
        return $this->getOwner()->defaultFieldAttribute($field);
    }

    public function callFieldAttributeMethod(BaseField $field, string $methodName, array $args)
    {
        return $this->getOwner()->callFieldAttributeMethod($field, $methodName, $args);
    }

    /**
     * Set a field with a given name.
     *
     * @param string $method
     * @param array  $args
     * @return self
     */
    public function __call(string $method, array $args)
    {
        if (\preg_match('/^(.*)FieldAttribute$/', $method, $matches)) {
            return $this->callFieldAttributeMethod(\array_shift($args), $matches[1], $args);
        }

        throw new \Exception("The method [$method] does not exist.");
    }
}
