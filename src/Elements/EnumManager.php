<?php
/**
 * Define an enum field manager used by Laramore.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Elements;

class EnumManager extends BaseManager
{
    protected $elementClass = Enum::class;
}
