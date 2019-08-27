<?php
/**
 * Define a basic field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Laramore\{
    Meta, Type, Builder
};
use Laramore\Validations\NotNullable;
use Laramore\Traits\Field\HasRules;

abstract class Field extends BaseField
{
    use HasRules {
        HasRules::addRule as private addRuleFromHasRule;
    }

    protected $attname;
    protected $default;
    protected $unique;
    protected $rules;

    /**
     * Set of rules.
     * Common to all fields.
     *
     * @var integer
     */

    // Indicate that no rules are applied.
    public const NONE = 0;

    // Indicate that the field accepts nullable values.
    public const NULLABLE = 1;

    // Except if trying to set a nullable value.
    public const NOT_NULLABLE = 2;

    // Indicate if it is visible by default.
    public const VISIBLE = 4;

    // Indicate if it is fillable by default.
    public const FILLABLE = 8;

    // Indicate if it is required by default.
    public const REQUIRED = 16;

    // Default rules for this type of field.
    public const DEFAULT_FIELD = (self::VISIBLE | self::FILLABLE | self::REQUIRED);

    protected static $defaultRules = self::DEFAULT_FIELD;

    /**
     * Create a new field with basic rules.
     * The constructor is protected so the field is created writing left to right.
     * ex: Text::field()->maxLength(255) insteadof (new Text)->maxLength(255).
     *
     * @param integer|string|array $rules
     */
    protected function __construct($rules=null)
    {
        $this->addRules($rules ?: static::$defaultRules);
    }

    /**
     * Call the constructor and generate the field.
     *
     * @param  array|integer|null $rules
     * @return static
     */
    public static function field($rules=null)
    {
        return new static($rules);
    }

    /**
     * Return the type object of the field.
     *
     * @return Type
     */
    abstract public function getType(): Type;

    /**
     * Return a property by its name.
     *
     * @param  string $key
     * @return mixed
     * @throws \ErrorException If no property exists with this name.
     */
    public function getProperty(string $key)
    {
        if ($key === 'type') {
            return $this->getType();
        }

        return parent::getProperty($key);
    }

    /**
     * Return the main property keys.
     *
     * @return array
     */
    public function getPropertyKeys(): array
    {
        return [
            'nullable', 'default', 'unique'
        ];
    }

    /**
     * Return the main properties.
     *
     * @return array
     */
    public function getProperties(): array
    {
        $properties = [];

        foreach ($this->getPropertyKeys() as $property) {
            $nameKey = explode(':', $property);
            $name = $nameKey[0];
            $key = ($nameKey[1] ?? $name);

            if (!is_null($value = $this->$key)) {
                $properties[$name] = $value;
            }
        }

        return $properties;
    }

    /**
     * Define the name property.
     *
     * @param  string $name
     * @return self
     */
    public function name(string $name)
    {
        parent::name($name);

        // The attribute name is by default the same as the field name.
        if (is_null($this->attname)) {
            $this->attname = $name;
        }

        return $this;
    }

    /**
     * Define this field as required or not.
     *
     * @param  boolean $required
     * @return self
     */
    public function required(bool $required=true)
    {
        $this->needsToBeUnlocked();

        if ($required) {
            return $this->addRule(self::REQUIRED);
        } else {
            return $this->removeRule(self::REQUIRED);
        }

        return $this;
    }

    /**
     * Define this field as fillable.
     *
     * @param  boolean $fillable
     * @return self
     */
    public function fillable(bool $fillable=true)
    {
        $this->needsToBeUnlocked();

        if ($fillable) {
            return $this->addRule(self::FILLABLE);
        } else {
            return $this->removeRule(self::FILLABLE);
        }

        return $this;
    }

    /**
     * Define this field as visible.
     *
     * @param  boolean $visible
     * @return self
     */
    public function visible(bool $visible=true)
    {
        $this->needsToBeUnlocked();

        if ($visible) {
            return $this->addRule(self::VISIBLE);
        } else {
            return $this->removeRule(self::VISIBLE);
        }

        return $this;
    }

    /**
     * Define the field as not visible.
     *
     * @param  boolean $hidden
     * @return self
     */
    public function hidden(bool $hidden=true)
    {
        return $this->visible(!$hidden);
    }

    /**
     * Define the field as nullable.
     *
     * @param  boolean $nullable
     * @return self
     */
    public function nullable(bool $nullable=true)
    {
        $this->needsToBeUnlocked();

        if ($nullable) {
            $this->addRuleFromHasRule(self::NULLABLE);
            $this->removeRule(self::NOT_NULLABLE | self::REQUIRED);
        } else {
            $this->removeRule(self::NULLABLE);
        }

        $this->defineProperty('nullable', $nullable);

        return $this;
    }

    /**
     * Define a default value for this field.
     *
     * @param  mixed $value
     * @return self
     */
    public function default($value=null)
    {
        $this->needsToBeUnlocked();

        if (is_null($value)) {
            $this->nullable();
        }

        $this->defineProperty('default', $this->castValue($model, $value));

        return $this;
    }

    /**
     * Check it is owned correctly.
     *
     * @return void
     */
    protected function owned()
    {
        if (!($this->getOwner() instanceof Meta) && !($this->getOwner() instanceof CompositeField)) {
            throw new \LogicException('A field should be owned by a Meta or a CompositeField');
        }
    }

    /**
     * Check all properties and rules before locking the field.
     *
     * @return void
     */
    protected function checkRules()
    {
        if ($this->hasProperty('default')) {
            if (is_null($this->default)) {
                if ($this->hasRule(self::NOT_NULLABLE)) {
                    throw new \LogicException("This field cannot be null and defined as null by default");
                } else if (!$this->hasRule(self::NULLABLE) && !$this->hasRule(self::REQUIRED)) {
                    throw new \LogicException("This field cannot be null, defined as null by default and not required");
                }
            }
        }

        if ($this->hasRule(self::NOT_NULLABLE)) {
            if ($this->hasRule(self::NULLABLE)) {
                throw new \LogicException("This field cannot be nullable and not nullable or strict on the same time");
            }
        }
    }

    protected function setValidations()
    {
        if ($this->hasRule(self::NOT_NULLABLE)) {
            $this->setValidation(NotNullable::class);
        }
    }

    /**
     * Add a rule to the resource.
     *
     * @param integer $rule
     * @return self
     */
    protected function addRule(int $rule)
    {
        $this->needsToBeUnlocked();

        if ($this->rulesContain($rule, self::NULLABLE)) {
            $this->nullable();
        }

        return $this->addRuleFromHasRule($rule);
    }

    /**
     * Return the casted value for a specific model object.
     *
     * @param  Model $model
     * @param  mixed $value
     * @return mixed
     */
    public function getValue(Model $model, $value)
    {
        return $this->castValue($model, $value);
    }

    public function transformValue(Model $model, $value)
    {
        return $this->castValue($model, $value);
    }

    /**
     * Return the value to set for a specific model object after passing all checks.
     *
     * @param Model $model
     * @param mixed $value
     * @return mixed
     */
    public function setValue(Model $model, $value)
    {
        $value = $this->transformValue($model, $value);
        $this->checkValue($model, $value);

        return $value;
    }

    /**
     * Return the query with this field as condition.
     *
     * @param  Builder $query
     * @param  mixed   ...$args
     * @return Builder
     */
    public function whereValue(Builder $query, ...$args)
    {
        return $query->where($this->name, ...$args);
    }
}
