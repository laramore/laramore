<?php
/**
 * Handle all observers for a specific grammar.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Grammars;

use Illuminate\Database\Grammar;
use Illuminate\Database\Schema\Blueprint;
use Laramore\Observers\BaseManager;
use Laramore\Contracts\Manager\LaramoreManager;

class GrammarTypeManager extends BaseManager implements LaramoreManager
{
    /**
     * Allowed observable sub class.
     *
     * @var string
     */
    protected $managedClass = Grammar::class;

    /**
     * The observable handler class to generate.
     *
     * @var string
     */
    protected $handlerClass = GrammarTypeHandler::class;

    /**
     * Observe all model events with our observers.
     *
     * @return void
     */
    protected function locking()
    {
        foreach ($this->getHandlers() as $handler) {
            foreach ($handler->all() as $types) {
                foreach ($types->all() as $type) {
                    Blueprint::macro($type, function ($column) use ($type) {
                        /** @var Blueprint $this */
                        return $this->addColumn($type, $column);
                    });
                }
            }
        }

        parent::locking();
    }
}
