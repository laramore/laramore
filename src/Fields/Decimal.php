<?php
/**
 * Define a float field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Fields;

use Laramore\Contracts\Field\NumericField;
use Laramore\Traits\Field\NumberInteraction;


class Decimal extends BaseAttribute implements NumericField
{
    use NumberInteraction;

    protected $totalDigits;
    protected $decimalDigits;

    /**
     * Define the precision of this float field.
     *
     * @param integer $totalDigits
     * @param integer $decimalDigits
     * @return self
     */
    public function digits(int $totalDigits=null, int $decimalDigits=null)
    {
        $this->needsToBeUnlocked();

        if (\func_num_args() == 1) {
            $this->decimalDigitsPrecision = $totalDigits;
        } else {
            $this->totalDigits = $totalDigits;
            $this->decimalDigits = $decimalDigits;
        }

        return $this;
    }

    /**
     * Dry the value in a simple format.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function dry($value)
    {
        return is_null($value) ? $value : (float) $value;
    }

    /**
     * Hydrate the value in a simple format.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function hydrate($value)
    {
        return is_null($value) ? $value : (float) $value;
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
}
