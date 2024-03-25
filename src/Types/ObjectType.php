<?php

namespace AshC\JsonAsserter\Types;

final readonly class ObjectType implements ComplexType
{
    public function __construct(
        public array $schema
    ) {
    }
}
