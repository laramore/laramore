<?php
/**
 * Add rule management for fields.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Traits\Field;

trait HasRules
{
    /**
     * Indicate if a rule is in a sum of rules.
     *
     * @param  integer $rules
     * @param  integer $rule
     * @return boolean
     */
    protected function rulesContain(int $rules, int $rule): bool
    {
        return ($rules & $rule) === $rule;
    }

    /**
     * Indicate if the resource has a rule or its joker.
     *
     * @param  integer      $rule
     * @param  integer|null $jokerRule
     * @return boolean
     */
    public function hasRule(int $rule, int $jokerRule=null)
    {
        return $this->rulesContain($this->rules, $rule)
            || (!is_null($jokerRule) && $this->rulesContain($this->rules, $jokerRule));
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

        $this->rules |= $rule;

        return $this;
    }

    /**
     * Add multiple rules to the resource.
     *
     * @param integer|string|array $rules
     * @return self
     */
    public function addRules($rules)
    {
        foreach ((array) $rules as $rule) {
            $this->addRule(is_int($rule) ? $rule : $this->getRule($rule));
        }

        return $this;
    }

    /**
     * Remove a rule from the resource.
     *
     * @param  integer $rule
     * @return self
     */
    protected function removeRule(int $rule)
    {
        $this->needsToBeUnlocked();

        $this->rules ^= ($this->rules & $rule);

        return $this;
    }

    /**
     * Return a rule value.
     *
     * @param  string $name
     * @return integer
     */
    protected function getRule(string $name): int
    {
        return constant(static::class.'::'.$name);
    }

    /**
     * List all available rules for this resource.
     *
     * @return array
     */
    public static function getAvailableRules(): array
    {
        $reflectionClass = new \ReflectionClass(static::class);
        $rules = $reflectionClass->getConstants();

        asort($rules);

        return $rules;
    }

    /**
     * List all current rules for this resource.
     *
     * @return array
     */
    public function getRules(): array
    {
        $rules = static::getAvailableRules();

        foreach ($rules as $key => $value) {
            if (!is_int($value) || !$this->hasRule($value)) {
                unset($rules[$key]);
            }
        }

        return $rules;
    }
}
