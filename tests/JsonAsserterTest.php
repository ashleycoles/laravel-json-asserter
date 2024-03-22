<?php

require_once 'vendor/autoload.php';

use AshC\JsonAsserter\Exceptions\InvalidJsonTypeException;
use AshC\JsonAsserter\JsonAsserter;
use PHPUnit\Framework\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;



class JsonAsserterTest extends TestCase
{
    use JsonAsserter;

    public function test_assertJsonHelper_dataContainsInvalidType(): void
    {
        $assertableJson = AssertableJson::fromArray([
            'message' => 'test'
        ]);

        $this->expectException(InvalidJsonTypeException::class);

        $this->assertJsonHelper($assertableJson, [
            'message' => 'invalid'
        ]);
    }

    public function test_assertJsonHelper_dataContainsAllValidTypes(): void
    {
        $assertableJson = AssertableJson::fromArray([
            'string' => 'test',
            'bool' => true,
            'int' => 1,
            'decimal' => 1.2,
            'null' => null,
            'array' => [1,2,3]
        ]);

        $this->assertJsonHelper($assertableJson, [
            'string' => 'string',
            'bool' => 'boolean',
            'int' => 'integer',
            'decimal' => 'double',
            'null' => 'null',
            'array' => 'array'
        ]);
    }

    public function test_assertJsonHelper_nestedSimpleObject(): void
    {
        $assertableJson = AssertableJson::fromArray([
            'message' => 'test data',
            'data' => [
                'id' => 1,
                'name' => 'abc'
            ]
        ]);

        $this->assertJsonHelper($assertableJson, [
            'message' => 'string',
            'data' => [
                'values' => [
                    'id' => 'integer',
                    'name' => 'string'
                ]
            ]
        ]);
    }

    public function test_assertJsonHelper_missingField(): void
    {
        $assertableJson = AssertableJson::fromArray([
            'message' => 'test'
        ]);

        $this->assertJsonHelper($assertableJson, [
            'message' => 'string',
            'test' => 'missing'
        ]);
    }
}