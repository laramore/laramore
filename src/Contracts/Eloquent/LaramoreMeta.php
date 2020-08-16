<?php
/**
 * Laramore meta.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Contracts\Eloquent;

use Laramore\Contracts\{
    Prepared, Field\FieldsOwner, Field\Field
};
use Laramore\Proxies\ProxyHandler;
use Laramore\Fields\Constraint\ConstraintHandler;

interface LaramoreMeta extends Prepared, FieldsOwner
{
    /**
     * Return the table name.
     *
     * @return string
     */
    public function getTableName(): string;

    /**
     * Define the table name.
     *
     * @param string $tableName
     * @return self
     */
    public function setTableName(string $tableName);

    /**
     * Return the model class.
     *
     * @return string
     */
    public function getModelClass(): string;

    /**
     * Get the model short name.
     *
     * @return string|null
     */
    public function getModelClassName();

    /**
     * Return the proxy handler for this meta.
     *
     * @return ProxyHandler
     */
    public function getProxyHandler(): ProxyHandler;

    /**
     * Return the relation handler for this meta.
     *
     * @return ConstraintHandler
     */
    public function getConstraintHandler(): ConstraintHandler;

    /**
     * Define a primary constraint.
     *
     * @param  Field|array<Field> $constrainted
     * @param  string             $name
     * @return self
     */
    public function primary($constrainted, string $name=null);

    /**
     * Define a index constraint.
     *
     * @param  Field|array<Field> $constrainted
     * @param  string             $name
     * @return self
     */
    public function index($constrainted, string $name=null);

    /**
     * Define a unique constraint.
     *
     * @param  Field|array<Field> $constrainted
     * @param  string             $name
     * @return self
     */
    public function unique($constrainted, string $name=null);
}
