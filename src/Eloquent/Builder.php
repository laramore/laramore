<?php
/**
 * Custom Builder to handle specific functionalities.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Eloquent;

use Illuminate\Database\Eloquent\Builder as BuilderBase;
use Laramore\Contracts\Eloquent\LaramoreBuilder;
use Laramore\Traits\Eloquent\HasLaramoreBuilder;

class Builder extends BuilderBase implements LaramoreBuilder
{
    use HasLaramoreBuilder;
}
