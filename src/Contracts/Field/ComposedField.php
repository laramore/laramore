<?php
/**
 * Define an composed field contract.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Contracts\Field;

interface ComposedField extends ExtraField, FieldsOwner
{
    /**
     * Decompose all fields by models.
     *
     * @return array<string,array<Field>>
     */
    public function decompose(): array;
}
