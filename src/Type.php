<?php

namespace AshC\JsonAsserter;

use AshC\JsonAsserter\Types\ArrayType;
use AshC\JsonAsserter\Types\ObjectType;

enum Type: string
{
    case STRING = 'string';
    case INTEGER = 'integer';
    case DOUBLE = 'double';
    case BOOLEAN = 'boolean';
    case NULL = 'null';
    case ARRAY = 'array';
    case MISSING = 'missing';

    public static function ARRAY(int $count, ?array $schema = null): ArrayType
    {
        return new ArrayType($count, $schema);
    }

    public static function OBJECT(array $schema): ObjectType
    {
        return new ObjectType($schema);
    }
}
