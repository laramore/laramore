<?php
/**
 * Define a timestamp field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Laramore\Facades\TypeManager;
use Laramore\Type;

class Timestamp extends Field
{
    protected $useCurrent;

    public function getType(): Type
    {
        return TypeManager::timestamp();
    }

    public function getPropertyKeys(): array
    {
        return array_merge(parent::getPropertyKeys(), [
            'useCurrent'
        ]);
    }

    protected function checkRules()
    {
        parent::checkRules();

        if (!($this->hasRule(self::NULLABLE) ^ $this->useCurrent)) {
            throw new \Exception("This field must be either nullable or set by default as the current date");
        }
    }

    public function cast($value)
    {
        return \is_null($value) ? null : new Carbon($value);
    }

    public function dry($value)
    {
        return \is_null($value) ? null : (string) $value;
    }

    public function transform($value)
    {
        return $value;
    }
}
