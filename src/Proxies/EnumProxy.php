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
use Laramore\Elements\EnumElement;
use Laramore\Fields\Enum;

class EnumProxy extends FieldProxy
{
    /**
     * The enum element defining the proxy.
     *
     * @var EnumElement
     */
    protected $enum;

    /**
     * An observer needs at least a name and a Closure.
     *
     * @param Enum        $field
     * @param EnumElement $element
     * @param string      $methodName
     * @param boolean     $static
     * @param boolean     $needsValue
     * @param string      $nameTemplate
     * @param string      $multiNameTemplate
     */
    public function __construct(Enum $field, EnumElement $element, string $methodName,
                                bool $static=false, bool $needsValue=false,
                                string $nameTemplate=null, string $multiNameTemplate=null)
    {
        $this->setEnum($element);

        parent::__construct($field, $methodName, $static, $needsValue, $nameTemplate, $multiNameTemplate);
    }

    /**
     * Parse the name with proxy data.
     *
     * @param string $nameTemplate
     * @return string
     */
    protected function parseName(string $nameTemplate): string
    {
        return Str::replaceInTemplate(
            $nameTemplate,
            [
                'elementname' => Str::camel($this->getEnum()->native),
                'identifier' => $this->getIdentifier(),
                'methodname' => $this->getMethodName(),
            ],
        );
    }

    /**
     * Define the proxy enum.
     *
     * @param EnumElement $enum
     * @return self
     */
    public function setEnum(EnumElement $enum)
    {
        $this->needsToBeUnlocked();

        $this->enum = $enum;

        return $this;
    }

    /**
     * Return the proxy enum.
     *
     * @return EnumElement
     */
    public function getEnum(): EnumElement
    {
        return $this->enum;
    }

    /**
     * Resolve one time the callback and save it so it can be callable.
     *
     * @param mixed ...$args
     * @return mixed
     */
    public function resolveCallback(...$args)
    {
        $result = parent::resolveCallback($this->enum, ...$args);

        $resolvedCallaback = $this->getCallback();
        $this->callback = function (...$args) use ($resolvedCallaback) {
            return \call_user_func($resolvedCallaback, $this->enum, ...$args);
        };

        return $result;
    }
}
