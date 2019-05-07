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
}
