# laravel-json-asserter

A prototype tool to make using the Laravel Fluent JSON testing API easier to use.

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
        'message' => Type::STRING,
        'data' => Type::ARRAY(1, [
            'id' => Type::INTEGER,
            'name' => Type::STRING,
            'age' => Type::INTEGER,
            'start_date' => Type::STRING,
            'contract' => Type::OBJECT([
                    'id' => Type::INTEGER,
                    'name' => Type::STRING
            ]),
            'certifications' => Type::ARRAY(1, [
                    'id' => Type::INTEGER,
                    'name' => Type::STRING,
                    'description' => Type::STRING
            ])
        ]);
    ]);
});
```

## Usage

Use the `JsonAsserter` trait in your test classes (or TestCase.php to automatically apply to all tests).

The `JsonAsserter` trait provides just one public method you need to know about, `assertJsonHelper(AssertableJson $json, array $schema): void`.

It also provides a `Type` enum you can use to assert the datatypes in your JSON responses.

```php
use AshC\JsonAsserter\JsonAsserter;

class ExampleTest extends TestCase
{
    use JsonAsserter;
    
    public function test_example(): void
    {
        $response = getJson('/api/example');
        
        $response->assertJson(function (AssertableJson) {
            $this->assertJsonHelper([
                'example' => Type::STRING
            ]);
        });
    }
}
```

### Available types:
- `Type::STRING`
- `Type::INTEGER`
- `Type::DOUBLE`
- `Type::BOOLEAN`
- `Type::NULL`
- `Type::ARRAY`
- `Type::MISSING`

### Type methods:
- `Type::OBJECT(array $schema)`
  - Used to assert the structure of an object
    ```php
    $this->assertJsonHelper($assertableJson, [
        'message' => Type::STRING,
        'data' => Type::OBJECT([
            'id' => Type::INTEGER,
            'name' => Type::STRING,
        ]),
    ]);
    ```
- `Type::ARRAY(int $count, ?array $schema)`
  - Used to assert the number of array items, and the structure of each item
    ```php
    $this->assertJsonHelper($assertableJson, [
        'data' => Type::ARRAY(2, [
            'id' => Type::INTEGER,
            'name' => Type::STRING,
        ]),
    ]);
    ```
  - If no `$schema` is provided then only the length of the array will be tested
