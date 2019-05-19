<?php
/**
 * Define a field types used by Laramore.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore;

class Type
{
    /**
     * Each constant defines a type for Laramore and its value in a database.
     *
     * @var string
     */
    public const BOOLEAN = 'boolean';
    public const INCREMENT = 'increments';
    public const NUMBER = 'integer';
    public const UNSIGNED = 'unsigned';
    public const TEXT = 'text';
    public const CHAR = 'string';
    public const DATETIME = 'datetime';
    public const TIMESTAMP = 'timestamp';
    public const UUID = 'uuid';
}
