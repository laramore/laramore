<?php
/**
 * Define a char field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

use Illuminate\Support\Facades\Schema;

class Char extends Text
{
    /**
     * Max length.
     *
     * @var integer
     */
    protected $length;

    /**
     * Define the max length for this field.
     *
     * @param integer $length
     *
     * @return self
     */
    public function length(int $length)
    {
        $this->needsToBeUnlocked();

        if ($length <= 0) {
            throw new \Exception('The max length must be a positive number');
        }

        $this->defineProperty('length', $length);

        return $this;
    }

    /**
     * Cast the value to correspond to the field desire.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function cast($value)
    {
        $value = parent::cast($value);

        if ($this->hasProperty('length') && \strlen($value) > $this->length) {
            return \substr($value, 0, $this->length);
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
        return $value;
    }

    /**
     * If no max length defined, get it from schema.
     *
     * @return void
     */
    protected function locking()
    {
        parent::locking();

        if (is_null($this->length)) {
            $this->length = Schema::getFacadeRoot()::$defaultStringLength;
        }
    }
}
