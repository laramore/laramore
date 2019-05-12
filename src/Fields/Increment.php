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

    protected static $defaultRules = self::DEFAULT_INCREMENT;

    public function getDefaultProperties(): array
    {
        return [
            'type' => 'increment',
        ];
    }

    protected function getMigrationMainProperties(): array
    {
        $properties = $this->getProperties();

        return [
            $properties['type'].'s' => $properties['attname'],
        ];
    }
}
