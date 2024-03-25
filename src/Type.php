<?php

namespace AshC\JsonAsserter;

enum Type: string
{
    case STRING = 'string';
    case INTEGER = 'integer';
    case DOUBLE = 'double';
    case BOOLEAN = 'boolean';
    case NULL = 'null';
    case ARRAY = 'array';
    case MISSING = 'missing';

    public static function ARRAY(int $count, array $schema): array
    {
        return [
            'count' => $count,
            'values' => $schema,
        ];
    }

    public static function OBJECT(array $schema): array
    {
        return [
            'values' => $schema,
        ];
    }
}
