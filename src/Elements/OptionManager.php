<?php
/**
 * Define a field option manager used by Laramore.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Elements;

use Laramore\Contracts\Manager\LaramoreManager;


class OptionManager extends LazyElementManager implements LaramoreManager
{
    /**
     * Option element class.
     *
     * @var string
     */
    protected $elementClass = OptionElement::class;

    /**
     * Path to load operators.
     *
     * @var string
     */
    protected $configPath = 'option.properties';

    /**
     * All option value names.
     * Default definitions.
     *
     * @var array<string>
     */
    protected $definitions = [
        'description' => null,
        'propagate' => true,
        'add' => [],
        'remove' => [],
    ];
}
