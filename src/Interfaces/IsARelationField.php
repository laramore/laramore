<?php
/**
 * Field interface.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Interfaces;

interface IsARelationField extends IsAField
{
    public function retrieve(IsALaramoreModel $model);

    public function consume(IsALaramoreModel $model, $value);

    public function relate(IsProxied $model);

    public function reverbate(IsALaramoreModel $model, $value): bool;
}
