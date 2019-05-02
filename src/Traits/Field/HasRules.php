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
     * @return static
     */
    protected function addRule(int $rule)
    {
        $this->checkLock();

        $this->rules |= $rule;

        return $this;
    }

    /**
     * Add multiple rules to the resource.
     *
     * @param integer|string|array $rule
     * @return static
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
     * @return static
     */
    protected function removeRule(int $rule)
    {
        $this->checkLock();

        if ($this->hasRule($rule)) {
            $this->rules ^= $rule;
        }

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
        $rules = self::getAvailableRules();

        foreach ($rules as $key => $value) {
            if (!is_int($value) || !$this->hasRule($value)) {
                unset($rules[$key]);
            }
        }

        return $rules;
    }
}
