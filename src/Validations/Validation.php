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

use Laramore\Observers\BaseObserver;
use Illuminate\Database\Eloquent\Model;
use Closure;

abstract class Validation extends BaseObserver
{
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
        return Str::snake((new \ReflectionClass($this))->getShortName());
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

    abstract public function getMessage(): string;
}
