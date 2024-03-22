<?php

declare(strict_types=1);

namespace AshC\JsonAsserter;

use AshC\JsonAsserter\Exceptions\InvalidJsonTypeException;
use Illuminate\Testing\Fluent\AssertableJson;

trait JsonAsserter
{
    public function assertJsonHelper(AssertableJson $json, array $structure): void
    {
        $topLevelKeys = array_keys($structure);
        $topLevelTypes = $this->getTopLevelTypes($structure);
        $nestedTypes = $this->getNestedTypes($structure);

        $json->hasAll($topLevelKeys)->whereAllType($topLevelTypes);

        if (!empty($nestedTypes)) {
            foreach ($nestedTypes as $field => $type) {
                $isTypeArray = isset($type['count']);
                if ($isTypeArray) {
                    $json->has($field, $type['count'], function (AssertableJson $json) use ($type) {
                        $nestedValues = $type['values'];
                        $this->assertJsonHelper($json, $nestedValues);
                    });
                } else {
                    $json->has($field, function (AssertableJson $json) use ($type) {
                        $nestedValues = $type['values'];
                        $this->assertJsonHelper($json, $nestedValues);
                    });
                }
            }
        }
    }

    /**
     * @throws InvalidJsonTypeException
     */
    private function getTopLevelTypes(array $structure): array
    {
        $validTypes = ['string', 'integer', 'boolean', 'double', 'array', 'null'];
        $topLevelTypes = [];

        foreach ($structure as $field => $type) {
            if (is_string($type)) {
                if (!in_array($type, $validTypes)) {
                    throw new InvalidJsonTypeException("Error '$type' is not a valid type. Available options are: " . implode(', ', $validTypes) );
                }
                $topLevelTypes[$field] = $type;
            }
        }
        return $topLevelTypes;
    }

    private function getNestedTypes(array $structure): array
    {
        $nestedTypes = [];

        foreach ($structure as $field => $type) {
            if (is_array($type)) {
                $nestedTypes[$field] = $type;
            }
        }

        return $nestedTypes;
    }
}
