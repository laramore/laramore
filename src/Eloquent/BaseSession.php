<?php
/**
 * Laramore example session.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2021
 * @license MIT
 */

namespace Laramore\Eloquent;

use Laramore\Traits\Eloquent\SessionMeta;

class BaseSession extends BaseModel
{
    protected static $userClass = \App\Models\User::class;

    use SessionMeta;
}
