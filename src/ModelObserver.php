<?php
/**
 * Observe all model events.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore;

use Illuminate\Database\Eloquent\Model;

class ModelObserver
{
    protected $meta;
    protected $observed = false;

    protected static $events = [
        'retrieved', 'creating', 'created', 'updating', 'updated',
        'saving', 'saved', 'restoring', 'restored', 'replicating',
        'deleting', 'deleted', 'forceDeleted',
    ];

    public function __construct(Meta $meta)
    {
        $this->meta = $meta;
    }

    public function observeAllEvents()
    {
        if ($this->observed) {
            throw new \Exception('Cannot be observed twice');
        }

        foreach (static::$events as $event) {
            if (\method_exists($this, $event)) {
                foreach ((array) $this->$event() as $callback) {
                    $this->meta->getModelClass()::$event($callback);
                }
            }
        }

        $this->observed = true;

        return $this;
    }

    protected function saving()
    {
        return [
            'default' => function (Model $model) {
                $attributes = $model->getAttributes();

                foreach ($this->meta->getFields() as $field) {
                    if (!isset($attributes[$attname = $field->attname])) {
                        if ($field->hasProperty('default')) {
                            $model->setAttribute($attname, $field->default);
                        }
                    }
                }
            },
            'required' => function (Model $model) {
                $missingFields = \array_diff($this->meta->getRequiredFields(), array_keys($model->getAttributes()));

                foreach ($missingFields as $key => $field) {
                    if ($this->meta->getField($field)->nullable) {
                        unset($missingFields[$key]);
                    }
                }

                if (count($missingFields)) {
                    throw new \Exception('Fields required: '.implode(', ', $missingFields));
                }
            },
        ];
    }
}
