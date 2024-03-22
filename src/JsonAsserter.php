<?php

declare(strict_types=1);

namespace AshC\JsonAsserter;

use AshC\JsonAsserter\Exceptions\InvalidJsonTypeException;
use Illuminate\Testing\Fluent\AssertableJson;

trait JsonAsserter
{
    public function assertJsonHelper(AssertableJson $json, array $schema): void
    {
        $topLevelTypes = $this->getTopLevelTypes($schema);
        $nestedTypes = $this->getNestedTypes($schema);

        $json->hasAll(array_keys($schema))->whereAllType($topLevelTypes);

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

    /**
     * @throws InvalidJsonTypeException
     */
    private function getTopLevelTypes(array $schema): array
    {
        return array_filter($schema, function ($type) {
            if (! is_string($type)) {
                return false;
            }

            if (! $this->isTypeValid($type)) {
                throw new InvalidJsonTypeException("Error '$type' is not a valid type. Available options are: ".implode(', ', $validTypes));
            }

            return true;
        });
    }

    private function getNestedTypes(array $schema): array
    {
        return array_filter($schema, function ($type) {
            return is_array($type);
        });
    }

    private function isTypeValid(string $type): bool
    {
        $validTypes = ['string', 'integer', 'boolean', 'double', 'array', 'null'];
        return in_array($type, $validTypes);
    }
}
