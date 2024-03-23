<?php

namespace AshC\JsonAsserter;

enum Type: string
{
    case STRING = 'string';
    case INTEGER = 'integer';
    case DOUBLE = 'double';
    case BOOLEAN = 'boolean';
    CASE NULL = 'null';
    CASE ARRAY = 'array';
    CASE MISSING = 'missing';

}
