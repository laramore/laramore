<?php
/**
 * Define all basic field methods.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Fields;

use Illuminate\Support\Str;
use Laramore\Observers\ModelObserver;
use Laramore\Interfaces\IsAField;
use Laramore\Traits\{
    IsOwnedAndLocked, HasProperties
};
use Laramore\Meta;

abstract class BaseField implements IsAField
{
    use IsOwnedAndLocked, HasProperties {
        setProperty as protected forceProperty;
    }

    protected $nullable;

    public function getProperty(string $key)
    {
        if ($this->hasProperty($key)) {
            if (\method_exists($this, $method = 'get'.\ucfirst($key))) {
                return \call_user_func([$this, $method]);
            }

            return $this->$key;
        } else if (\defined($const = 'static::'.\strtoupper(Str::snake($key)))) {
            return $this->hasRule(\constant($const));
        }

        throw new \Exception("The property $key does not exist");
    }

    public function setProperty(string $key, $value)
    {
        $this->needsToBeUnlocked();

        return $this->forceProperty($key, $value);
    }

    /**
     * Define the name of the field.
     *
     * @param  string $name
     * @return static
     */
    public function name(string $name)
    {
        $this->needsToBeUnlocked();

        if (!is_null($this->name)) {
            throw new \Exception('The field name cannot be defined multiple times');
        }

        $this->name = $name;
    }

    public function getMeta()
    {
        do {
            $owner = $this->getOwner();
        } while (!($owner instanceof Meta));

        return $owner;
    }

    public function getModelClass()
    {
        return $this->getMeta()->getModelClass();
    }

    protected function addObserver(ModelObserver $observer)
    {
        $this->getMeta()->getModelObservableHandler()->addObserver($observer);

        return $this;
    }

    public function getRelationValue($model)
    {
        return $this->whereValue($model, $model->{$this->name});
    }
}
