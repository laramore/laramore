<?php
/**
 * Laramore example session meta.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2021
 * @license MIT
 */

namespace Laramore\Traits\Eloquent;

use Illuminate\Support\Facades\Schema;
use Laramore\Contracts\Eloquent\LaramoreMeta;
use Laramore\Fields\{
    Integer, Char, Text
};

trait SessionMeta
{
    protected static function generateUserField(LaramoreMeta $meta)
    {
        $meta->user = static::$userFieldClass::field()->on(static::$userClass)->nullable()->index();
    }

    public static function meta(LaramoreMeta $meta)
    {
        $meta->id = Char::field()->primary()->maxLength(Schema::getFacadeRoot()::$defaultStringLength);

        static::generateUserField($meta);

        $meta->ipAddress = Char::field()->maxLength(45)->nullable();
        $meta->userAgent = Text::field()->nullable();
        $meta->payload = Text::field();
        $meta->lastActivity = Integer::field()->index();
    }
}
