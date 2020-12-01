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

use Laramore\Contracts\Field\IncrementField;
use Laramore\Traits\Field\Increments;

class Increment extends Integer implements IncrementField
{
    use Increments;
}
