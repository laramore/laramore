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
use Laramore\Meta;
use Laramore\Traits\Field\HasRules;

abstract class Field extends BaseField
{
    use HasRules {
        HasRules::addRule as private addRuleFromHasRule;
    }

    protected $rules;

    /**
     * Set of rules.
     * Common to all fields.
     *
     * @var integer
     */

    // Indicate that no rules are applied
    public const NONE = 0;

    // Strict mode: will throw an exception for each error. Pass over everthing
    public const STRICT = 1;

    // Indicate that the field accepts nullable values
    public const NULLABLE = 2;

    // Except if trying to set a nullable value
    public const NOT_NULLABLE = 4;

    // Indicate it is visible by default
    public const VISIBLE = 8;

    // Indicate it is fillable by default
    public const FILLABLE = 16;

    // Indicate it is required by default
    public const REQUIRED = 32;

    // Default rules
    public const DEFAULT_FIELD = (self::NOT_NULLABLE | self::VISIBLE | self::FILLABLE);

    protected static $defaultRules = self::DEFAULT_FIELD;

    protected function __construct($rules=null)
    {
        $this->properties = $this->getDefaultProperties();

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

    public function name(string $name)
    {
        parent::name($name);

        // The attribute name is by default the same as the field name.
        if (!$this->hasProperty('attname')) {
            $this->properties['attname'] = $name;
        }

        return $this;
    }

    public function required(bool $required=true): array
    {
        $this->checkLock();

        if ($required) {
            return $this->addRule(self::REQUIRED);
        } else {
            return $this->removeRule(self::REQUIRED);
        }
    }

    public function fillable(bool $fillable=true)
    {
        $this->checkLock();

        if ($fillable) {
            return $this->addRule(self::FILLABLE);
        } else {
            return $this->removeRule(self::FILLABLE);
        }
    }

    public function visible(bool $visible=true)
    {
        $this->checkLock();

        if ($visible) {
            return $this->addRule(self::VISIBLE);
        } else {
            return $this->removeRule(self::VISIBLE);
        }

        return $this;
    }

    public function hidden(bool $hidden=true)
    {
        return $this->visible(!$hidden);
    }

    public function nullable(bool $nullable=true)
    {
        $this->checkLock();

        if ($nullable) {
            $this->addRuleFromHasRule(self::NULLABLE);
            $this->removeRule(self::NOT_NULLABLE);
        } else {
            $this->removeRule(self::NULLABLE);
        }

        $this->properties['nullable'] = $nullable;

        return $this;
    }

    public function default($value=null)
    {
        $this->checkLock();

        if (is_null($value)) {
            $this->nullable();
        }

        $this->properties['default'] = $this->castValue($value);

        return $this;
    }

    protected function owning()
    {
        if (!($this->getOwner() instanceof Meta) && !($this->getOwner() instanceof CompositeField)) {
            throw new \Exception('A field should be owned by a Meta or a CompositeField');
        }
    }

    protected function locking()
    {
        if ($this->hasProperty('default') && is_null($this->default)) {
            if ($this->hasRule(self::NOT_NULLABLE, self::STRICT)) {
                throw new \Exception("This field cannot be null and defined as null by default");
            } else if (!$this->hasRule(self::NULLABLE) && !$this->hasRule(self::REQUIRED, self::STRICT)) {
                throw new \Exception("This field cannot be null, defined as null by default and not required");
            }
        }

        if ($this->hasRule(self::NULLABLE) && $this->hasRule(self::NOT_NULLABLE, self::STRICT)) {
            throw new \Exception("This field cannot be nullable and not nullable or strict on the same time");
        }
    }

    protected function addRule(int $rule)
    {
        $this->checkLock();

        if ($this->rulesContain($rule, self::NULLABLE)) {
            $this->nullable();
        }

        return $this->addRuleFromHasRule($rule);
    }

    public function castValue($value)
    {
        return $value;
    }

    public function getValue($model, $value)
    {
        return $this->castValue($value);
    }

    public function setValue($model, $value)
    {
        $value = $this->castValue($value);

        if (is_null($value) && $this->hasRule(self::NOT_NULLABLE, self::STRICT)) {
            throw new \Exception($this->name.' can not be null');
        }

        return $value;
    }

    public function relationValue($model)
    {
        return $this->whereValue($model, $model->{$this->name});
    }

    public function whereValue($query, ...$args)
    {
        return $query->where($this->name, ...$args);
    }
}
