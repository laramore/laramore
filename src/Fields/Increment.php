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

use Laramore\Facades\TypeManager;
use Laramore\Type;

class Increment extends Number
{
    // Default rules
    public const DEFAULT_INCREMENT = (self::VISIBLE | self::NOT_ZERO | self::UNSIGNED | self::POSITIVE | self::CORRECT_SIGN);

    protected static $defaultRules = self::DEFAULT_INCREMENT;

    public function getType(): Type
    {
        return TypeManager::increment();
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
