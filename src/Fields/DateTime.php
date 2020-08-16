<?php
/**
 * Define a datetime field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Fields;

use Carbon\Carbon;
use Laramore\Elements\TypeElement;
use Laramore\Facades\{
    Option, Type
};

class DateTime extends BaseAttribute
{
    protected $format;

    const TIMESTAMP_FORMAT = 'timestamp';

    /**
     * Return the type of the field.
     *
     * @return TypeElement
     */
    public function getType(): TypeElement
    {
        if ($this->getFormat(static::TIMESTAMP_FORMAT)) {
            return Type::timestamp();
        }

        return $this->resolveType();
    }

    /**
     * Return the format for serialization.
     *
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format ?: $this->getConfig('format');
    }

    /**
     * Define this field as a timestamp.
     *
     * @return self
     */
    public function timestamp()
    {
        $this->format(static::TIMESTAMP_FORMAT);

        return $this;
    }

    /**
     * Indicate if this field is timestamped.
     *
     * @return boolean
     */
    public function isTimestamped(): bool
    {
        return $this->getType() === Type::timestamp();
    }

    /**
     * Check all properties and options before locking the field.
     *
     * @return void
     */
    protected function checkOptions()
    {
        parent::checkOptions();

        if ($this->hasOption(Option::nullable()) && $this->hasOption(Option::useCurrent())) {
            throw new \Exception("This field must be either nullable or set by default as the current value");
        }
    }

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
     * Hydrate the value in a simple format.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function hydrate($value)
    {
        return \is_null($value) ? $value : new Carbon($value);
    }

    /**
     * Cast the value in the correct format.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function cast($value)
    {
        return \is_null($value) ? $value : new Carbon($value);
    }

    /**
     * Serialize the value for outputs.
     *
     * @param  mixed $value
     * @return mixed
     */
    public function serialize($value)
    {
        if (\is_null($value)) {
            return $value;
        }

        $format = $this->getFormat();

        if ($format === Type::timestamp()->native) {
            return $value->getTimestamp();
        }

        return $value->format($this->getFormat());
    }
}
