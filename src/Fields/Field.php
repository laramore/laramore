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
use Laramore\Interfaces\IsAField;
use Laramore\Traits\Field\HasRules;

abstract class Field implements IsAField
{
    use HasRules {
        HasRules::addRule as private addRuleFromHasRule;
    }

    protected $rules;
    protected static $readOnlyProperties = [
        'type'
    ];
    protected $properties = [];

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

    protected function __construct($rules='DEFAULT_FIELD')
    {
        $this->addRules($rules);
    }

    public static function field(...$args)
    {
        return new static(...$args);
    }

    public function __call(string $method, array $args)
    {
        $this->checkLock();

        if (count($args) === 0) {
            $this->setProperty($method, true);
        } else if (count($args) === 1) {
            $this->setProperty($method, $args[0]);
        } else {
            $this->setProperty($method, $args);
        }

        return $this;
    }

    public function __get(string $key)
    {
        return $this->getProperty($key);
    }

    public function __set(string $key, $value)
    {
        return $this->setProperty($key, $value);
    }

    public function __isset(string $key): bool
    {
        return $this->hasProperty($key);
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function hasProperty(string $key): bool
    {
        return isset($this->properties[$key]);
    }

    public function getProperty(string $key)
    {
        if (property_exists($this, $key)) {
            return $this->$key;
        } else if ($this->hasProperty($key)) {
            return $this->properties[$key];
        } else if (defined($const = 'self::'.strtoupper(Str::snake($key)))) {
            return $this->hasRule(constant($const));
        } else {
            throw new \Exception('Value does not exist');
        }
    }

    public function setProperty(string $key, $value)
    {
        $this->checkLock();

        if (in_array($key, static::$readOnlyProperties)) {
            throw new \Exception("The propery $key cannot be set");
        } else if (method_exists($this, $key)) {
            $this->$key($value);
        } else {
            $this->properties[$key] = $value;
        }

        return $this;
    }

    public function name(string $name)
    {
        $this->checkLock();

        $this->properties['name'] = $name;

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

    protected function locking()
    {
        if (is_null($this->default)) {
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
