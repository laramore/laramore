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

use Laramore\Contracts\Eloquent\LaramoreMeta;
use Laramore\Fields\{
    Integer, ManyToOne, PrimaryId, Char, Text
};

trait SessionMeta
{
    protected static function generateIdField(LaramoreMeta $meta)
    {
        $meta->id = PrimaryId::field();
    }

    protected static function generateUserField(LaramoreMeta $meta)
    {
        $meta->user = ManyToOne::field()->on(static::$userClass)->nullable()->index();
    }

    public static function meta(LaramoreMeta $meta)
    {
        static::generateIdField($meta);
        static::generateUserField($meta);

        $meta->ipAddress = Char::field()->maxLength(45)->nullable();
        $meta->userAgent = Text::field()->nullable();
        $meta->payload = Text::field();
        $meta->lastActivity = Integer::field()->index();
    }
}
