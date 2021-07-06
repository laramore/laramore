<?php
/**
 * Define a list field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2021
 * @license MIT
 */

namespace Laramore\Fields;

use Laramore\Elements\ValueCollection;


class JsonList extends Json
{
    protected $collectionType = ValueCollection::LIST_COLLECTION;
}
