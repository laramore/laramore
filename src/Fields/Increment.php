<?php
/**
 * Define an increment field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

use Laramore\Type;

class Increment extends Number
{
    protected $type = Type::INCREMENT;

    // Default rules
    public const DEFAULT_INCREMENT = (self::VISIBLE | self::NOT_ZERO | self::UNSIGNED | self::POSITIVE | self::CORRECT_SIGN);

    protected static $defaultRules = self::DEFAULT_INCREMENT;

    public function getType(): string
    {
        return $this->type;
    }

    public function getPropertyKeys(): array
    {
        $keys = parent::getPropertyKeys();

        if (!is_null($index = array_search('unsigned', $keys))) {
            unset($keys[$index]);
        }

        return $keys;
    }
}
