<?php
/**
 * Handle all observers for a specific grammar.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Observers;

use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Database\Schema\Blueprint;

class GrammarObservableManager extends BaseObservableManager
{
    protected $observableSubClass = Grammar::class;
    protected $observableHandlerClass = GrammarObservableHandler::class;

    /**
     * Observe all model events with our observers.
     *
     * @return void
     */
    protected function locking()
    {
    	$observed = array_unique(array_merge(...array_map(function ($observableHandler) {
    		return $observableHandler->getObserved();
    	}, $this->observableHandlers) ?? []));

    	foreach ($observed as $type) {
	        Blueprint::macro($type, function ($column) use ($type) {
	            return $this->addColumn($type, $column);
	        });
    	}

        parent::locking();
    }
}
