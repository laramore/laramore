<?php
/**
 * Handle all observers for a specific model.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Validations;

use Illuminate\Database\Eloquent\Model;
use Laramore\Validations\ValidationErrorBag;
use Laramore\Fields\BaseField;
use Laramore\Observers\{
    BaseObserver, BaseHandler
};

class ValidationHandler extends BaseHandler
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
    protected function push(BaseObserver $observer, array &$observers)
    {
        if (!isset($observers[$name = $observer->getField()->name])) {
            $observers[$name] = [];
        }

        return parent::push($observer, $observers[$name]);
    }

    /**
     * Return if an observe exists with the given name.
     *
     * @param  string $fieldName
     * @param  string $name
     * @return boolean
     */
    public function has(string $fieldName, string $name=null): bool
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
     * Return the first observer with the given name.
     *
     * @param  string $fieldName
     * @param  string $name
     * @return mixed
     */
    public function get(string $fieldName, string $name=null)
    {
        if (is_null($name)) {
            return $this->observers[$fieldName];
        }

        foreach ($this->observers[$fieldName] as $key => $observer) {
            if ($observer->getName() === $name) {
                return $observer;
            }
        }

        throw new \Exception('The observer does not exist');
    }

    /**
     * Return the list of the handled observers.
     *
     * @param  string $fieldName
     * @return array
     */
    public function all(string $fieldName=null): array
    {
        if (is_null($fieldName)) {
            return $this->observers;
        }

        return $this->get($fieldName);
    }

    public function getValidationErrors(BaseField $field, $value): ValidationErrorBag
    {
        $bag = new ValidationErrorBag;
        $priority = Validation::MAX_PRIORITY;

        if ($this->has($field->name)) {
            foreach ($this->all($field->name) as $validation) {
                // Validation can fail only with same priorities.
                if ($priority !== $validation->getPriority()) {
                    if ($bag->count()) {
                        break;
                    }

                    $priority = $validation->getPriority();
                }

                if (!$validation->isValueValid($value)) {
                    foreach ((array) $validation->getMessage() as $message) {
                        $bag->add($validation->getName(), $message);
                    }
                }
            }
        }

        return $bag;
    }
}
