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
use Laramore\Observers\BaseObserver;
use Laramore\Traits\HasProperties;
use Closure;

abstract class Validation extends BaseObserver
{
    use HasProperties;

    /**
     * An observer needs at least a name and a callback.
     *
     * @param mixed   $field
     * @param integer $priority
     */
    public function __construct($field, int $priority=self::MEDIUM_PRIORITY)
    {
        $this->observe($field);
        $this->setPriority($priority);
    }

    protected function locking()
    {
    }

    public function getName(): string
    {
        return static::getStaticName();
    }

    public static function getStaticName(): string
    {
        return Str::snake((new \ReflectionClass(static::class))->getShortName());
    }

    /**
     * Return the callback function.
     *
     * @return Closure
     */
    public function getCallback(): Closure
    {
        return [$this, 'isValueValid'];
    }

    public function getFieldName(): string
    {
        return $this->getObserved()[0];
    }

    abstract public function isValueValid(Model $model, $value): bool;

    abstract public function getMessage();
}
