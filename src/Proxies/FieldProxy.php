<?php
/**
 * A proxy defines the field to use with which method to call.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Proxies;

use Illuminate\Support\Str;
use Laramore\Contracts\Field\Field;
use Closure;

class FieldProxy extends IdentifiedProxy
{
    /**
     * The field to use for the call.
     *
     * @var Field
     */
    protected $field;

    /**
     * Indicate if the proxy needs to resolve the value.
     *
     * @var bool
     */
    protected $needsValue;

    /**
     * An observer needs at least a name and a Closure.
     *
     * @param Field   $field
     * @param string  $methodName
     * @param boolean $static
     * @param boolean $allowMulti
     * @param boolean $needsValue
     * @param string  $nameTemplate
     * @param string  $multiNameTemplate
     */
    public function __construct(Field $field, string $methodName, bool $static=false, bool $allowMulti=true,
                                bool $needsValue=false, string $nameTemplate=null, string $multiNameTemplate=null)
    {
        parent::__construct($field->getName(), $methodName, $static, $allowMulti, $nameTemplate, $multiNameTemplate);

        $this->setField($field);
        $this->setNeedToResolveValue($needsValue);
        $this->setCallback(Closure::fromCallable([$this, 'resolveCallback']));
    }

    /**
     * Define the proxy field.
     *
     * @param Field $field
     * @return self
     */
    public function setField(Field $field)
    {
        $this->needsToBeUnlocked();

        $this->field = $field;
        $this->setIdentifier($field->getName());

        return $this;
    }

    /**
     * Return the proxy field.
     *
     * @return Field
     */
    public function getField(): Field
    {
        return $this->field;
    }

    /**
     * Define if the proxy needs to resolve the model value.
     *
     * @param boolean $needsValue
     * @return self
     */
    public function setNeedToResolveValue(bool $needsValue)
    {
        $this->needsToBeUnlocked();

        $this->needsValue = $needsValue;

        return $this;
    }

    /**
     * Indicate if the proxy needs to resolve the model value.
     *
     * @return array
     */
    public function needsToResolveValue(): bool
    {
        return $this->needsValue;
    }

    /**
     * Actions during locking.
     *
     * @return void
     */
    protected function locking()
    {
        if ($this->needsToResolveValue() && $this->isStatic()) {
            throw new \LogicException("The field proxy `{$this->getName()}` cannot be static and resolve values");
        }

        parent::locking();
    }

    /**
     * Parse the method owner name with proxy data.
     *
     * @param string $nameTemplate
     * @return string
     */
    protected function parseMethodOwnerName(string $nameTemplate): string
    {
        return Str::replaceInTemplate(
            $nameTemplate,
            [
                'name' => $this->getName(),
                'identifier' => $this->getIdentifier(),
                'methodname' => $this->getMethodName(),
            ],
        );
    }

    /**
     * Resolve one time the callback and save it so it can be callable.
     *
     * @param mixed ...$args
     * @return mixed
     */
    public function resolveCallback(...$args)
    {
        $field = $this->getField();
        $owner = $field->getOwner();
        $methodOwnerName = $this->parseMethodOwnerName(config('field.templates.method_owner'));

        $this->callback = function (...$args) use ($owner, $field, $methodOwnerName) {
            return \call_user_func([$owner, $methodOwnerName], $field, ...$args);
        };

        return \call_user_func($this->getCallback(), ...$args);
    }

    /**
     * Call the proxy.
     *
     * @param  mixed ...$args
     * @return mixed
     */
    public function __invoke(...$args)
    {
        $this->checkArguments($args);

        if ($this->isStatic()) {
            \array_shift($args);
        }

        if ($this->needsToResolveValue()) {
            $args[0] = $this->getField()->get($args[0]);
        }

        return \call_user_func($this->getCallback(), ...$args);
    }
}
