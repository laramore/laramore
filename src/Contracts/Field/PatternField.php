<?php
/**
 * Define a pattern field contract.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Contracts\Field;

interface PatternField extends Field
{
    /**
     * Return the pattern to match.
     *
     * @return string
     */
    public function getPattern(): string;

    /**
     * Return all pattern flags
     *
     * @return mixed
     */
    public function getPatternFlags();
}
