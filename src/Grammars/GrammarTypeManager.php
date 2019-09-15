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

class GrammarTypeManager extends BaseManager
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
        $observed = array_unique(array_merge([], ...array_values(array_map(function ($handler) {
            return array_merge([], ...array_map(function ($observer) {
                return $observer->all();
            }, $handler->all()));
        }, $this->handlers))));

        foreach ($observed as $type) {
            Blueprint::macro($type, function ($column) use ($type) {
                return $this->addColumn($type, $column);
            });
        }

        parent::locking();
    }
}
