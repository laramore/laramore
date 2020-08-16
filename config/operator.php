<?php

namespace Laramore\Elements;

return [

    /*
    |--------------------------------------------------------------------------
    | Operator manager
    |--------------------------------------------------------------------------
    |
    | This option defines the class that will manage all operators.
    |
    */

    'manager' => OperatorManager::class,

    /*
    |--------------------------------------------------------------------------
    | Default operators
    |--------------------------------------------------------------------------
    |
    | This option defines all default operators.
    | The operators must be defined as keys.
    | There are callable for example like this:
    | > whereNumberInfEq(10) to specify that the field `number` must be inferior or equal to 10.
    |
    | The value could be:
    | - a `native` string: it is the operator used in database,
    | - an array with:
    |   - a `native` string (by default it is set with the key value),
    |   - a `value_type` definition: force the value to be `null`, a `collection` or a `binary`.
    |
    */

    'configurations' =>  [
        'null' => [
            'value_type' => OperatorElement::NULL_TYPE,
        ],
        'not_null' => [
            'native' => 'not null',
            'value_type' => OperatorElement::NULL_TYPE,
        ],
        'doesnt_exist' => [
            'native' => 'null',
            'value_type' => OperatorElement::NULL_TYPE,
        ],
        'dont_exist' => [
            'native' => 'null',
            'value_type' => OperatorElement::NULL_TYPE,
        ],
        'exist' => [
            'native' => 'not null',
            'value_type' => OperatorElement::NULL_TYPE,
        ],
        'exists' => [
            'native' => 'not null',
            'value_type' => OperatorElement::NULL_TYPE,
        ],
        'equal' => [
            'native' => '=',
        ],
        'eq' => [
            'native' => '=',
        ],
        'inf' => [
            'native' => '<',
            'value_type' => OperatorElement::NUMERIC_TYPE,
        ],
        'sup' => [
            'native' => '>',
            'value_type' => OperatorElement::NUMERIC_TYPE,
        ],
        'inf_eq' => [
            'native' => '<=',
            'value_type' => OperatorElement::NUMERIC_TYPE,
        ],
        'sup_eq' => [
            'native' => '>=',
            'value_type' => OperatorElement::NUMERIC_TYPE,
        ],
        'safe_not_equal' => [
            'native' => '<>',
            'fallback' => '!=',
        ],
        'not_eq' => [
            'native' => '!=',
            'fallback' => '!=',
        ],
        'not_equal' => [
            'native' => '!=',
            'fallback' => '!=',
        ],
        'different' => [
            'native' => '!=',
            'fallback' => '!=',
        ],
        'safe_equal' => [
            'native' => '<=>',
        ],
        'like' => [
            'native' => 'like',
        ],
        'like_binary' => [
            'native' => 'like binary',
        ],
        'not_nike' => [
            'native' => 'not like',
            'fallback' => '!=',
        ],
        'ilike' => [
            'native' => 'ilike',
        ],
        'not_ilike' => [
            'native' => 'not ilike',
            'fallback' => '!=',
        ],
        'rlike' => [
            'native' => 'rlike',
        ],
        'regexp' => [
            'native' => 'regexp',
        ],
        'not_regexp' => [
            'native' => 'not regexp',
            'fallback' => '!=',
        ],
        'similar_to' => [
            'native' => 'similar to',
        ],
        'not_timilar_to' => [
            'native' => 'not similar to',
            'fallback' => '!=',
        ],
        'bitand' => [
            'native' => '&',
            'value_type' => OperatorElement::BINARY_TYPE,
        ],
        'bitor' => [
            'native' => '|',
            'value_type' => OperatorElement::BINARY_TYPE,
        ],
        'bitxor' => [
            'native' => '^',
            'value_type' => OperatorElement::BINARY_TYPE,
        ],
        'bitleft' => [
            'native' => '<<',
            'value_type' => OperatorElement::BINARY_TYPE,
        ],
        'bitright' => [
            'native' => '>>',
            'value_type' => OperatorElement::BINARY_TYPE,
        ],
        'match' => [
            'native' => '~',
        ],
        'imatch' => [
            'native' => '~*',
        ],
        'not_match' => [
            'native' => '!~',
            'fallback' => '!=',
        ],
        'not_imatch' => [
            'native' => '!~*',
            'fallback' => '!=',
        ],
        'same' => [
            'native' => '~~',
        ],
        'isame' => [
            'native' => '~~*',
        ],
        'not_same' => [
            'native' => '!~~',
            'fallback' => '!=',
        ],
        'not_isame' => [
            'native' => '!~~*',
            'fallback' => '!=',
        ],
        'in' => [
            'native' => 'in',
            'value_type' => OperatorElement::COLLECTION_TYPE,
        ],
        'not_in' => [
            'native' => 'not in',
            'value_type' => OperatorElement::COLLECTION_TYPE,
            'fallback' => '!=',
        ],
    ],

];
