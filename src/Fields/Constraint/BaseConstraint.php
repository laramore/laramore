<?php
/**
 * Define a field constraint.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields\Constraint;

use Illuminate\Support\{
    Str, Facades\Event
};
use Laramore\Contracts\Configured;
use Laramore\Contracts\Field\{
    AttributeField, ComposedField, Field, Constraint\Constraint
};
use Laramore\Observers\BaseObserver;

abstract class BaseConstraint extends BaseObserver implements Constraint, Configured
{
    /**
     * An observer needs at least a name.
     *
     * @param Field|array<Field> $fields
     * @param string             $name
     * @param integer            $priority
     */
    protected function __construct($fields, string $name=null, int $priority=self::MEDIUM_PRIORITY)
    {
        if (!\is_null($name)) {
            $this->setName($name);
        }

        $this->on($fields);
        $this->setPriority($priority);

        if ($this->count() === 0) {
            throw new \LogicException('A constraints works on at least one field');
        }
    }

    /**
     * Define a new constraint.
     *
     * @param Field|array<Field> $fields
     * @param string             $name
     * @param integer            $priority
     * @return self|null
     */
    public static function constraint($fields, string $name=null,
                                      int $priority=self::MEDIUM_PRIORITY)
    {
        $creating = Event::until('constraints.creating', static::class, \func_get_args());

        if ($creating === false) {
            return null;
        }

        $constraint = $creating ?: new static($fields, $name, $priority);

        Event::dispatch('constraints.created', $constraint);

        return $constraint;
    }

    /**
     * Unpack composed fields to get all composed and attributes fields.
     *
     * @param array $fields
     * @return array
     */
    protected function unpackFields(array $fields): array
    {
        $unpackedFields = [];

        foreach ($fields as $field) {
            $unpackedFields[] = $field;

            if ($field instanceof ComposedField) {
                $unpackedFields = \array_merge(
                    $unpackedFields,
                    $this->unpackFields($field->getFields(ComposedField::class)),
                    $field->getFields(AttributeField::class)
                );
            }
        }

        return $unpackedFields;
    }

    /**
     * Add one or more fields to observe.
     *
     * @param  string|array $fields
     * @return self
     */
    public function on($fields)
    {
        return parent::on($this->unpackFields(\is_array($fields) ? $fields : [$fields]));
    }

    /**
     * Return the configuration path for this field.
     *
     * @param string $path
     * @return mixed
     */
    public function getConfigPath(string $path=null)
    {
        $name = Str::snake((new \ReflectionClass($this))->getShortName());

        return 'field.constraint.configurations.'.$name.(\is_null($path) ? '' : '.'.$path);
    }

    /**
     * Return the configuration for this field.
     *
     * @param string $path
     * @param mixed  $default
     * @return mixed
     */
    public function getConfig(string $path=null, $default=null)
    {
        return config($this->getConfigPath($path), $default);
    }

    /**
     * Return the constraint name.
     *
     * @return string
     */
    public function getConstraintType(): string
    {
        return $this->getConfig('type');
    }

    /**
     * Return the default name for this constraint.
     *
     * @return string
     */
    public function getDefaultName(): string
    {
        $tableName = $this->getTableNames()[0];

        return $tableName.'_'.implode('_', \array_map(function (AttributeField $field) {
            return $field->getAttname();
        }, $this->getAttributes())).'_'.$this->getConstraintType();
    }

    /**
     * Indicate if it has a name.
     *
     * @return boolean
     */
    public function hasName(): bool
    {
        return !\is_null($this->name);
    }

    /**
     * Return the name of this constraint.
     *
     * @return string
     */
    public function getName(): string
    {
        if (!$this->hasName()) {
            return $this->getDefaultName();
        }

        return $this->name;
    }

    /**
     * Return all table names related to this constraint.
     *
     * @return array<string>
     */
    public function getTableNames(): array
    {
        return \array_unique(\array_map(function (AttributeField $field) {
            return $field->getMeta()->getTableName();
        }, $this->getAttributes()));
    }

    /**
     * Return all concerned fields.
     *
     * @return array
     */
    public function getFields(): array
    {
        return $this->all();
    }

    /**
     * Return all concerned attribute fields.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return \array_values(\array_filter($this->getFields(), function ($field) {
            return $field instanceof AttributeField;
        }));
    }

    /**
     * Indicate if this constraint is composed of multiple fields.
     *
     * @return boolean
     */
    public function isComposed(): bool
    {
        return \count($this->getAttributes()) > 1;
    }

    /**
     * Return the only field.
     *
     * @return Field
     */
    public function getField(): Field
    {
        if ($this->isComposed()) {
            throw new \Exception('Cannot get attribute from composed constraint');
        }

        return $this->getField()[0];
    }

    /**
     * Return the only attribute.
     *
     * @return AttributeField
     */
    public function getAttribute(): AttributeField
    {
        if ($this->isComposed()) {
            throw new \Exception('Cannot get attribute from composed constraint');
        }

        return $this->getAttributes()[0];
    }

    /**
     * Disallow any modifications after locking the instance.
     *
     * @return self
     */
    public function lock()
    {
        $locking = Event::until('constraints.locking', $this);

        if ($locking === false) {
            return $this;
        }

        parent::lock();

        Event::dispatch('constraints.locked', $this);

        return $this;
    }
}
