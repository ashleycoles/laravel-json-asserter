<?php

namespace AshC\JsonAsserter\Types;

final readonly class ArrayType implements ComplexType
{
    public function __construct(
        public int $count,
        public ?array $schema = null
    ) {
    }
}
