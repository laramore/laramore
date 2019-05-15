<?php
/**
 * Define a timestamp field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

class Timestamp extends Field
{
    public function getDefaultProperties(): array
    {
        return [
            'type' => 'timestamp',
        ];
    }

    protected function locking()
    {
        parent::locking();

        if (!($this->hasRule(self::NULLABLE) ^ $this->useCurrent)) {
            throw new \Exception("This field must be either nullable or set by default as the current date");
        }
    }

    protected function getMigrationNameProperties(): array
    {
        return array_merge(
            parent::getMigrationNameProperties(),
            [
                'useCurrent'
            ]
        );
    }
}
