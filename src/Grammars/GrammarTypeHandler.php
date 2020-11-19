<?php
/**
 * Handle all observers for a specific model.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Grammars;

use Laramore\Observers\BaseHandler;

class GrammarTypeHandler extends BaseHandler
{
    /**
     * The observer class to use to generate.
     *
     * @var string
     */
    protected $observerClass = GrammarType::class;

    /**
     * Observe all model events with our observers.
     *
     * @return void
     */
    protected function locking()
    {
        foreach ($this->observers as $observer) {
            foreach ($observer->all() as $type) {
                $this->observableClass::macro('type'.ucfirst($type), $observer->getCallback());
            }
        }

        parent::locking();
    }
}
