<?php
/**
 * Define an operator manager used for SQL operations.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Elements;

use Laramore\Contracts\Manager\LaramoreManager;


class OperatorManager extends LazyElementManager implements LaramoreManager
{
    /**
     * Operator element class.
     *
     * @var string
     */
    protected $elementClass = OperatorElement::class;

    /**
     * Path to load operators.
     *
     * @var string
     */
    protected $configPath = 'operator.properties';

    /**
     * All operator value names.
     * Default definitions.
     *
     * @var array<string>
     */
    protected $definitions = [
        'description' => null,
        'value_type' => OperatorElement::MIXED_TYPE,
        'fallback' => '=',
    ];
}
