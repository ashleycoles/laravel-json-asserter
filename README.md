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

Using the fluent JSON assertion system in Laravel to test the structure of the JSON we produce a test that looks like this

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

Compared to the prototype assertion helper

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

It uses an array to describe the struture and datatypes of the JSON, and then uses the Laravel fluent JSON testing API to generate the same exact assertions as the vanilla example above
