<?php
/**
 * Define a object field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2021
 * @license MIT
 */

namespace Laramore\Fields;

use Laramore\Elements\ValueCollection;


class JsonObject extends Json
{
    protected $collectionType = ValueCollection::OBJECT_COLLECTION;
}
