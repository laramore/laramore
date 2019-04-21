<?php
/**
 * Define a primary id field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */


namespace Laramore\Fields;

use Laramore\Interfaces\IsAPrimaryField;

class PrimaryId extends Increment implements IsAPrimaryField
{
    // Default rules
    public const DEFAULT_PRIMARY = (self::NOT_NULLABLE | self::VISIBLE | self::NOT_ZERO | self::UNSIGNED | self::POSITIVE | self::CORRECT_SIGN);

    protected static $defaultRules = self::DEFAULT_PRIMARY;
}
