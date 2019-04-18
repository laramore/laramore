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

class Increment extends Number
{
    // Default rules
    public const DEFAULT_INCREMENT = (self::VISIBLE | self::NOT_ZERO | self::UNSIGNED | self::POSITIVE | self::CORRECT_SIGN);

    protected function __construct($rules='DEFAULT_INCREMENT')
    {
        parent::__construct($rules);
    }

    public function getDefaultProperties(): array
    {
        return [
            'type' => 'increment',
        ];
    }
}
