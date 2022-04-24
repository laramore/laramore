<?php
/**
 * Define hash field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2022
 * @license MIT
 */

namespace Laramore\Fields;

use Illuminate\Support\Facades\Hash;
use Laramore\Contracts\Eloquent\LaramoreModel;

class Hashed extends Char
{
    /**
     * Cast the value to correspond to the field desire.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function cast($value)
    {
        $value = parent::cast($value);

        if (\is_null($value) || !Hash::needsRehash($value)) {
            return $value;
        }

        return $value;
    }

    /**
     * Set the value for the field.
     *
     * @param LaramoreModel|array|\ArrayAccess $model
     * @param  mixed                            $value
     * @return mixed
     */
    public function set($model, $value)
    {
        if ($model instanceof LaramoreModel && ! $model->fetchingDatabase) {
            $value = $this->hash($value);
        }

        return parent::set($model, $value);
    }

    /**
     * Hash the password so it is not retrievible.
     *
     * @param string $value
     * @return string
     */
    public function hash(string $value)
    {
        return Hash::make($value);
    }

    /**
     * Indicate if the password is the right one.
     *
     * @param LaramoreModel|array|\Illuminate\Contracts\Support\\ArrayAccess $model
     * @param string|null  $value
     * @param boolean $expected
     * @return boolean
     */
    public function check($model, ?string $value, bool $expected=true)
    {
        return Hash::check($value, $this->get($model)) == $expected;
    }
}
