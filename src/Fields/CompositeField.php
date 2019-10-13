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
use Laramore\Interfaces\{
    IsProxied, IsAFieldOwner, IsALaramoreModel, IsARelationField
};
use Laramore\Meta;

abstract class CompositeField extends BaseField implements IsAFieldOwner, IsARelationField
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

    // Default rules for this type of field.
    public const DEFAULT_COMPOSITE = (self::DEFAULT_FIELD ^ self::REQUIRED);

    protected static $defaultRules = self::DEFAULT_COMPOSITE;

    protected function __construct($rules=null, array $fields=null, array $links=null)
    {
        parent::__construct($rules);

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
     * Call the constructor and generate the field.
     *
     * @param  array|integer|null $rules
     * @return static
     */
    public static function field($rules=null, array $fields=null, array $links=null)
    {
        return new static($rules, $fields, $links);
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
            return $link::field();
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
        return isset($this->all()[$name]);
    }

    public function get(string $name)
    {
        if ($this->has($name)) {
            return $this->all()[$name];
        } else {
            throw new \Exception($name.' real or link field does not exist');
        }
    }

    public function all()
    {
        return array_merge(
	        $this->fields,
	        $this->links
        );
    }

    public function replaceInTemplate(string $template, array $keyValues)
    {
        foreach ($keyValues as $varName => $value) {
            $template = \str_replace('*{'.$varName.'}', Str::plural($value),
                \str_replace('^{'.$varName.'}', \ucwords($value),
                    \str_replace('${'.$varName.'}', $value, $template)
                )
            );
        }

        return $template;
    }

    /**
     * Add a rule to the resource.
     *
     * @param integer $rule
     * @return self
     */
    protected function addRule(int $rule)
    {
        foreach ($this->all() as $field) {
            $field->addRule($rule);
        }

        return parent::addRule($rule);
    }

    /**
     * Remove a rule from the resource.
     *
     * @param  integer $rule
     * @return self
     */
    protected function removeRule(int $rule)
    {
        foreach ($this->all() as $field) {
            $field->removeRule($rule);
        }

        return parent::removeRule($rule);
    }

    public function unique()
    {
        $this->needsToBeUnlocked();

        $this->unique[] = $this->getFields();

        return $this;
    }

    public function owned()
    {
        parent::owned();

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

    protected function checkRules()
    {

    }

    protected function setValidations()
    {

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

    public function getUnique()
    {
        return $this->unique;
    }

    /**
     * Return the get value for a specific field.
     *
     * @param BaseField        $field
     * @param IsALaramoreModel $model
     * @param mixed            $value
     * @return mixed
     */
    public function getFieldAttribute(BaseField $field, IsALaramoreModel $model)
    {
        return $this->getOwner()->getFieldAttribute($field, $model);
    }

    /**
     * Return the set value for a specific field.
     *
     * @param BaseField        $field
     * @param IsALaramoreModel $model
     * @param mixed            $value
     * @return mixed
     */
    public function setFieldAttribute(BaseField $field, IsALaramoreModel $model, $value)
    {
        return $this->getOwner()->setFieldAttribute($field, $model, $value);
    }

    /**
     * Return the set value for a specific field.
     *
     * @param BaseField        $field
     * @param IsALaramoreModel $model
     * @param mixed            $value
     * @return mixed
     */
    public function whereFieldAttribute(BaseField $field, IsProxied $model, $operator=null, $value=null, ...$args)
    {
        if (func_num_args() === 3) {
            return $this->getOwner()->whereFieldAttribute($field, $model, $operator);
        }

        return $this->getOwner()->whereFieldAttribute($field, $model, $operator, $value, ...$args);
    }

    /**
     * Return the set value for a specific field.
     *
     * @param BaseField $field
     * @param IsProxied $model
     * @param mixed     $value
     * @return mixed
     */
    public function relateFieldAttribute(BaseField $field, IsProxied $model)
    {
        return $this->getOwner()->relateFieldAttribute($field, $model);
    }

    /**
     * Return the set value for a specific field.
     *
     * @param BaseField        $field
     * @param IsALaramoreModel $model
     * @param mixed            $value
     * @return mixed
     */
    public function resetFieldAttribute(BaseField $field, IsALaramoreModel $model)
    {
        return $this->getOwner()->resetFieldAttribute($field, $model);
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
    public function serializeFieldAttribute(BaseField $field, $value)
    {
        return $this->getOwner()->serializeFieldAttribute($field, $value);
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
     * @param mixed     $value
     * @return mixed
     */
    public function getRelationFieldAttribute(IsARelationField $field, IsALaramoreModel $model)
    {
        return $this->getOwner()->getRelationFieldAttribute($field, $model);
    }

    /**
     * Return the set value for a specific field.
     *
     * @param IsARelationField $field
     * @param mixed            $value
     * @return mixed
     */
    public function setRelationFieldAttribute(IsARelationField $field, IsALaramoreModel $model, $value)
    {
        return $this->getOwner()->setRelationFieldAttribute($field, $model, $value);
    }

    /**
     * Reverbate a saved relation value for a specific field.
     *
     * @param IsARelationField $field
     * @param IsALaramoreModel $model
     * @param mixed            $value
     * @return boolean
     */
    public function reverbateRelationFieldAttribute(IsARelationField $field, IsALaramoreModel $model, $value): bool
    {
        return $this->getOwner()->reverbateRelationFieldAttribute($field, $model, $value);
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

        return parent::__call($method, $args);
    }
}
