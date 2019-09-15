<?php
/**
 * Define a basic validation rule.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Validations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laramore\Fields\BaseField;
use Laramore\Traits\HasProperties;
use Laramore\Observers\BaseObserver;
use Closure;

abstract class Validation extends BaseObserver
{
    use HasProperties;

    protected $field;

    /**
     * An observer needs at least a name and a Closure.
     *
     * @param BaseField $field
     * @param integer   $priority
     */
    public function __construct(BaseField $field, int $priority=self::MEDIUM_PRIORITY)
    {
        $this->setField($field);

        parent::__construct(static::getStaticName(), null, $priority);
    }

    public static function getStaticName(): string
    {
        return Str::snake((new \ReflectionClass(static::class))->getShortName());
    }

    /**
     * Define the proxy field.
     *
     * @param BaseField $field
     * @return self
     */
    public function setField(BaseField $field)
    {
        $this->needsToBeUnlocked();

        $this->field = $field;

        return $this;
    }

    public function getField()
    {
        return $this->field;
    }

    /**
     * Return the Closure function.
     *
     * @return Closure
     */
    public function getCallback(): Closure
    {
        return [$this, 'isValueValid'];
    }

    abstract public function isValueValid($value): bool;

    abstract public function getMessage();
}
