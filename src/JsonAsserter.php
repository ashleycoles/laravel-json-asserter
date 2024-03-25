<?php

declare(strict_types=1);

namespace AshC\JsonAsserter;

use AshC\JsonAsserter\Exceptions\InvalidJsonTypeException;
use AshC\JsonAsserter\Types\ArrayType;
use AshC\JsonAsserter\Types\ComplexType;
use AshC\JsonAsserter\Types\ObjectType;
use Illuminate\Testing\Fluent\AssertableJson;

trait JsonAsserter
{
    public function assertJsonHelper(AssertableJson $json, ?array $schema): void
    {
        $presentFields = $this->getPresentFields($schema);
        $missingFields = $this->getMissingFields($schema);
        $topLevelTypes = $this->getTopLevelTypes($schema);
        $nestedTypes = $this->getNestedTypes($schema);

        $json->hasAll($presentFields)->whereAllType($topLevelTypes)->missingAll($missingFields);

        foreach ($nestedTypes as $field => $type) {
            $assertions = function (AssertableJson $json) use ($type) {
                if ($type->schema) {
                    $this->assertJsonHelper($json, $type->schema);
                }
            };

            if ($type instanceof ObjectType) {
                $json->has($field, $assertions);
            } elseif ($type instanceof ArrayType && $type->schema) {
                $json->has($field, $type->count, $assertions);
            } else {
                $json->has($field, $type->count);
            }
        }
    }

    /**
     * @throws InvalidJsonTypeException
     */
    private function getTopLevelTypes(array $schema): array
    {
        $topLevelEnums = array_filter($schema, function (Type|ComplexType $type) {
            if ($this->isComplexType($type) || $type->value === 'missing') {
                return false;
            }

            return true;
        });

        return array_map(function (Type $type) {
            return $type->value;
        }, $topLevelEnums);
    }

    private function getNestedTypes(array $schema): array
    {
        return array_filter($schema, function (Type|ComplexType $type) {
            return $this->isComplexType($type);
        });
    }

    private function getMissingFields(array $schema): array
    {
        return array_keys(array_filter($schema, function (Type|ComplexType $type) {
            return ! $this->isComplexType($type) && $type->value === 'missing';
        }));
    }

    private function getPresentFields(array $schema): array
    {
        return array_keys(array_filter($schema, function (Type|ComplexType $type) {
            return ! $this->isComplexType($type) && $type->value !== 'missing';
        }));
    }

    private function isComplexType(mixed $type): bool
    {
        return $type instanceof ArrayType || $type instanceof ObjectType;
    }
}
