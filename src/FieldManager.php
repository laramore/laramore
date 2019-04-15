<?php
/**
 * Define all fields for a Meta.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore;

use Laramore\Fields\Field;

class FieldManager
{
    protected $meta;

    public function __construct($meta)
    {
        $this->meta = $meta;
    }

    public function __get($name)
    {
        return $this->meta->get($name);
    }

    public function __set($name, $value)
    {
        $this->meta->set($name, $value);
    }

    public function __call($method, $args)
    {
        $field = new class($method, $args[0]) extends Field {
            public function __construct($type, $name)
            {
                $this->type = $type;
                $this->name = $name;

                parent::__construct();
            }
        };

        $this->__set($args[0], $field);

        return $field;
    }
};
