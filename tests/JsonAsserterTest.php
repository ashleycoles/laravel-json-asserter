<?php

require_once 'vendor/autoload.php';

use AshC\JsonAsserter\JsonAsserter;
use AshC\JsonAsserter\Type;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\TestCase;

class JsonAsserterTest extends TestCase
{
    use JsonAsserter;

    public function test_assertJsonHelper_dataContainsAllValidTypes(): void
    {
        $assertableJson = AssertableJson::fromArray([
            'string' => 'test',
            'bool' => true,
            'int' => 1,
            'decimal' => 1.2,
            'null' => null,
            'array' => [1, 2, 3],
        ]);

        $this->assertJsonHelper($assertableJson, [
            'string' => Type::STRING,
            'bool' => Type::BOOLEAN,
            'int' => Type::INTEGER,
            'decimal' => Type::DOUBLE,
            'null' => Type::NULL,
            'array' => Type::ARRAY,
        ]);
    }

    public function test_assertJsonHelper_nestedSimpleObject(): void
    {
        $assertableJson = AssertableJson::fromArray([
            'message' => 'test data',
            'data' => [
                'id' => 1,
                'name' => 'abc',
            ],
        ]);

        $this->assertJsonHelper($assertableJson, [
            'message' => Type::STRING,
            'data' => Type::OBJECT([
                'id' => Type::INTEGER,
                'name' => Type::STRING,
            ]),
        ]);
    }

    public function test_assertJsonHelper_multiLevelNestedObjects(): void
    {
        $assertableJson = AssertableJson::fromArray([
            'message' => 'test data',
            'data' => [
                'id' => 1,
                'name' => 'abc',
                'test' => [
                    'test' => [
                        'foo' => 'bar',
                    ],
                ],
            ],
        ]);

        $this->assertJsonHelper($assertableJson, [
            'message' => Type::STRING,
            'data' => Type::OBJECT([
                'id' => Type::INTEGER,
                'name' => Type::STRING,
                'test' => Type::OBJECT([
                    'test' => Type::OBJECT([
                        'foo' => Type::STRING,
                    ]),
                ]),
            ]),
        ]);
    }

    public function test_assertJsonHelper_nestSimpleArray(): void
    {
        $assertableJson = AssertableJson::fromArray([
            'data' => [
                [
                    'id' => 1,
                    'name' => 'foo',
                ],
                [
                    'id' => 1,
                    'name' => 'bar',
                ],
            ],
        ]);

        $this->assertJsonHelper($assertableJson, [
            'data' => Type::ARRAY(2, [
                'id' => Type::INTEGER,
                'name' => Type::STRING,
            ]),
        ]);
    }

    public function test_assertJsonHelper_multipleNestedArrays(): void
    {
        $assertableJson = AssertableJson::fromArray([
            'data' => [
                [
                    'outer' => [
                        [
                            'inner' => [
                                'id' => 1,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertJsonHelper($assertableJson, [
            'data' => Type::ARRAY(1, [
                'outer' => Type::ARRAY(1, [
                    'inner' => Type::OBJECT([
                        'id' => Type::INTEGER,
                    ]),
                ]),
            ]),
        ]);
    }

    public function test_assertJsonHelper_missingField(): void
    {
        $assertableJson = AssertableJson::fromArray([
            'message' => 'test',
        ]);

        $this->assertJsonHelper($assertableJson, [
            'message' => Type::STRING,
            'test' => Type::MISSING,
            'other' => Type::MISSING,
        ]);
    }
}
