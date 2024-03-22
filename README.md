# laravel-json-asserter

A prototype tool to make writing and reading Laravel JSON assertions less painfull.

## Example

Take the following JSON structure

```json
{
    "message": "Employees retrieved",
    "data": [
        {
            "id": 1,
            "name": "Carolyn Aufderhar",
            "age": 46,
            "start_date": "2005-10-27",
            "contract": {
                "id": 1,
                "name": "Ullam earum."
            },
            "certifications": [
                {
                    "id": 1,
                    "name": "Error aspernatur.",
                    "description": "Et eos minima ut aliquam est. Odit quia quasi ut suscipit."
                }
            ]
        }
    ]
}
```

Using the fluent JSON assertion system in Laravel to test the structure of the JSON we produce a test that looks like this:

```php
$response->assertJson(function (AssertableJson $json) {
     $json->hasAll(['message', 'data'])
         ->whereType('message', 'string')
         ->has('data', 1, function (AssertableJson $json) {
             $json->hasAll(['id', 'name', 'age' , 'start_date', 'contract', 'certifications'])
                 ->whereAllType([
                     'id' => 'integer',
                     'name' => 'string',
                     'age' => 'integer',
                     'start_date' => 'string'
                 ])
                 ->has('contract', function (AssertableJson $json) {
                     $json->hasAll(['id', 'name'])
                         ->whereAllType([
                             'id' => 'integer',
                             'name' => 'string'
                         ]);
                 })
                 ->has('certifications', 1, function (AssertableJson $json) {
                     $json->hasAll(['id', 'name', 'description'])
                         ->whereAllType([
                             'id' => 'integer',
                             'name' => 'string',
                             'description' => 'string'
                         ]);
                 });
         });
});
```

Compared to the prototype assertion helper:

```php
$response->assertJson(function (AssertableJson $json) {
    $this->assertJsonHelper($json, [
        'message' => 'string',
        'data' => [
            'count' => 1,
            'values' => [
                'id' => 'integer',
                'name' => 'string',
                'age' => 'integer',
                'start_date' => 'string',
                'contract' => [
                    'values' => [
                        'id' => 'integer',
                        'name' => 'string'
                    ]
                ],
                'certifications' => [
                    'count' => 1,
                    'values' => [
                        'id' => 'integer',
                        'name' => 'string',
                        'description' => 'string'
                    ]
                ]
            ]
        ]
    ]);
});
```

## Usage

JsonAsserter uses an array to describe the structure and datatype of the JSON, and then uses the Laravel fluent JSON testing API behind the scenes to generate assertions.

For JSON fields that are simple data-types, you can use `string`, `integer`, `double`, `boolean` and `null` - the same as with Laravel's `whereType()` and `whereAllType()` methods.

In addition to the standard Laravel types, you can use a type of `missing` to assert that the field is absent from the response.

```php
[
    'name' => 'string',
    'age' => 'integer',
    'likes_fluent_json_testing_syntax' => 'boolean',
    'example' => 'missing'
]
```

For arrays and objects, you can use a nested array to describe the structure of the array/object.

For an object, the array must have a values subarray.

```php
[
    'contract' => [
        'values' => [
            'id' => 'integer',
            'name' => 'string'
        ]
    ]
]
```

For an array, the array must have both values and count. Count representing the number of results expected in the array.

```php
[
    'certifications' => [
        'count' => 1,
        'values' => [
            'id' => 'integer',
            'name' => 'string',
            'description' => 'string'
        ]
    ]
]
```

Nesting objects within arrays and vice versa is of course allowed.

```php
[
    'friends' => [
        'count' => 10,
        'values = [
            'id' => 'integer',
            'name' => 'string',
            'hobbies' => [
                'count' => 3,
                'values' => [
                    'id' => 'integer',
                    'hobby' => 'string',
                    'difficulty' => [
                        'values' => [
                            'name' => 'string',
                            'score' => 'double'
                        ]
                    ]
                ]
            ]
        ]
    ]
]
```
