<?php
/**
 * Handle all observers for a specific model.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Observers;

use Illuminate\Database\Eloquent\Model;
use Laramore\Validations\{
    Validation, ValidationErrorBag
};

class ValidationHandler extends BaseObservableHandler
{
    /**
     * The observable class.
     *
     * @var string
     */
    protected $observerClass = Validation::class;

    /**
     * Add an observer to a list of observers.
     *
     * @param BaseObserver $observer
     * @param array        $observers
     * @return self
     */
    protected function pushObserver(BaseObserver $observer, array &$observers)
    {
        if (!isset($observers[$name = $observer->getFieldName()])) {
            $observers[$name] = [];
        }

        return parent::pushObserver($observer, $observers[$name]);
    }

    /**
     * Return if an observe exists with the given name.
     *
     * @param  string $fieldName
     * @param  string $name
     * @return boolean
     */
    public function hasObserver(string $fieldName, string $name=null): bool
    {
        if (is_null($name)) {
            return isset($this->observers[$fieldName]);
        }

        foreach (($this->observers[$fieldName] ?? []) as $key => $observer) {
            if ($observer->getName() === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return if an observe exists with the given name.
     *
     * @param  string $fieldName
     * @param  string $name
     * @return boolean
     */
    public function hasValidation(string $fieldName, string $name=null): bool
    {
        return $this->hasObserver($fieldName, $name);
    }

    /**
     * Return the first observer with the given name.
     *
     * @param  string $fieldName
     * @param  string $name
     * @return BaseObserver
     */
    public function getObserver(string $fieldName, string $name=null): BaseObserver
    {
        if (is_null($name)) {
            throw new \Exception('The name information is required');
        }

        foreach ($this->observers[$fieldName] as $key => $observer) {
            if ($observer->getName() === $name) {
                return $observer;
            }
        }

        throw new \Exception('The observer does not exist');
    }

    /**
     * Return the first observer with the given name.
     *
     * @param  string $fieldName
     * @param  string $name
     * @return BaseObserver
     */
    public function getValidation(string $fieldName, string $name=null): BaseObserver
    {
        return $this->getObserver($fieldName, $name);
    }

    /**
     * Return the list of the handled observers.
     *
     * @param  string $fieldName
     * @return array
     */
    public function getObservers(string $fieldName=null): array
    {
        if (is_null($fieldName)) {
            return $this->observers;
        }

        return $this->observers[$fieldName];
    }

    /**
     * Return the list of the handled observers.
     *
     * @param  string $fieldName
     * @return array
     */
    public function getValidations(string $fieldName=null): array
    {
        return $this->getObservers($fieldName);
    }

    /**
     * Need to do anything.
     *
     * @return void
     */
    protected function locking()
    {

    }

    public function getValidationErrors(string $fieldName, Model $model, $value): ValidationErrorBag
    {
        $bag = new ValidationErrorBag;
        $priority = Validation::MAX_PRIORITY;

        if ($this->hasObserver($fieldName)) {
            foreach ($this->getObservers($fieldName) as $validation) {
                // Validation can fail only with same priorities.
                if ($priority !== $validation->getPriority()) {
                    if ($bag->count()) {
                        break;
                    }

                    $priority = $validation->getPriority();
                }

                if (!$validation->isValueValid($model, $value)) {
                    foreach ((array) $validation->getMessage() as $message) {
                        $bag->add($validation->getName(), $message);
                    }
                }
            }
        }

        return $bag;
    }
}
