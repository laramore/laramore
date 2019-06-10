<?php
/**
 * This exception indicate that we tried to edit an owned instance.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Exceptions;

use Laramore\Traits\Exception\HasElement;

class OwnException extends LaramoreException
{
    use HasElement;
}
