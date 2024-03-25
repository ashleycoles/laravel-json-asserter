<?php

declare(strict_types=1);

namespace AshC\JsonAsserter;

use AshC\JsonAsserter\Exceptions\InvalidJsonTypeException;
use Illuminate\Testing\Fluent\AssertableJson;

trait JsonAsserter
{
    public function assertJsonHelper(AssertableJson $json, array $schema): void
    {
        $presentFields = $this->getPresentFields($schema);
        $missingFields = $this->getMissingFields($schema);
        $topLevelTypes = $this->getTopLevelTypes($schema);
        $nestedTypes = $this->getNestedTypes($schema);

        $json->hasAll($presentFields)->whereAllType($topLevelTypes)->missingAll($missingFields);

        foreach ($nestedTypes as $field => $type) {
            $isTypeArray = isset($type['count']);

            $assertions = function (AssertableJson $json) use ($type) {
                $this->assertJsonHelper($json, $type['values']);
            };

            if ($isTypeArray) {
                $json->has($field, $type['count'], $assertions);
            } else {
                $json->has($field, $assertions);
            }
        }
    }

    /**
     * @throws InvalidJsonTypeException
     */
    private function getTopLevelTypes(array $schema): array
    {
        $topLevelEnums = array_filter($schema, function (Type|array $type) {
            if (is_array($type) || $type->value === 'missing') {
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
        return array_filter($schema, function (Type|array $type) {
            return is_array($type);
        });
    }

    private function getMissingFields(array $schema): array
    {
        return array_keys(array_filter($schema, function (Type|array $type) {
            return ! is_array($type) && $type->value === 'missing';
        }));
    }

    private function getPresentFields(array $schema): array
    {
        return array_keys(array_filter($schema, function (Type|array $type) {
            return ! is_array($type) && $type->value !== 'missing';
        }));
    }
}
