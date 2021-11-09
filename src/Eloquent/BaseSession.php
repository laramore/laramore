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
use Laramore\Fields\ManyToOne;

class BaseSession extends BaseModel
{
    use SessionMeta;

    protected static $userClass = \App\Models\User::class;

    protected static $userFieldClass = ManyToOne::class;
}
