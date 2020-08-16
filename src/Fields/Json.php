<?php
/**
 * Define a json field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Fields;

use Laramore\Elements\JsonCollection;

class Json extends BaseAttribute
{
    /**
     * Dry the value in a simple format.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function dry($value)
    {
        return \is_null($value) ? null : (string) $value;
    }

    /**
     * Hydrate the value in the correct format.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function hydrate($value)
    {
        if (\is_null($value)) {
            return $value;
        }

        if (\is_string($value)) {
            $value = \json_decode($value, true);
        }

        if (!($value instanceof JsonCollection)) {
            return new JsonCollection($value);
        }

        return $value;
    }

    /**
     * Cast the value in the correct format.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function cast($value)
    {
        if (\is_null($value)) {
            return $value;
        }

        if (\is_string($value)) {
            $value = \json_decode($value, true);
        }

        if (!($value instanceof JsonCollection)) {
            return new JsonCollection($value);
        }

        return $value;
    }

    /**
     * Serialize the value for outputs.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function serialize($value)
    {
        return $value->toArray();
    }
}
