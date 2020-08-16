<?php
/**
 * Laramore builder.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Contracts\Eloquent;

use Laramore\Contracts\Proxied;

interface LaramoreBuilder extends Proxied
{
    /**
     * Get the underlying query builder instance.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function getQuery();

    /**
     * Update a record in the database.
     *
     * @param  array $values
     * @return integer
     */
    public function update(array $values);

    /**
     * Add a basic where clause to the query.
     *
     * @param  string|array|\Closure $column
     * @param  mixed                 $operator
     * @param  mixed                 $value
     * @param  mixed|string          $boolean
     * @return self
     */
    public function where($column, $operator=null, $value=null, $boolean='and');

    /**
     * Multiple where conditions
     *
     * @param array        $column
     * @param mixed        $operator
     * @param mixed        $value
     * @param string|mixed $boolean
     * @return self
     */
    public function multiWhere(array $column, $operator=null, $value=null, $boolean='and');

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array|mixed $columns
     * @return \Laramore\Contracts\Eloquent\LaramoreCollection|static[]
     */
    public function get($columns=['*']);

    /**
     * Dry values.
     *
     * @param  array $values
     * @return mixed
     */
    public function dryValues(array $values);

    /**
     * Handles dynamic "where" clauses to the query.
     *
     * @param  string $where
     * @param  array  $parameters
     * @return self
     */
    public function dynamicWhere(string $where, array $parameters);

    /**
     * Add a relationship count / exists condition to the query.
     *
     * @param  string|mixed  $relation
     * @param  string|mixed  $operator
     * @param  integer|mixed $count
     * @param  string|mixed  $boolean
     * @param  \Closure|null $callback
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function has($relation, $operator='>=', $count=1, $boolean='and', \Closure $callback=null);

    /**
     * Add a relationship count / exists condition to the query.
     *
     * @param  string|mixed  $relation
     * @param  string|mixed  $boolean
     * @param  \Closure|null $callback
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function doesntHave($relation, $boolean='and', \Closure $callback=null);
}
