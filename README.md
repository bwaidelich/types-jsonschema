# types-jsonschema

Integration for the [wwwision/types](https://github.com/bwaidelich/types) package that allows to generate [JSON Schema](https://json-schema.org/) files from PHP code

## Usage

This package can be installed via [composer](https://getcomposer.org):

```bash
composer require wwwision/types-jsonschema
```

With that, you can create JSON Schema from PHP classes:

```php
class Contact {
    public function __construct(public string $name, public int $age) {}
}

$schema = JSONSchemaGenerator::fromClass(Contact::class);

$expected = <<<JSON
{
    "type": "object",
    "properties": {
        "name": {
            "type": "string"
        },
        "age": {
            "type": "integer"
        }
    },
    "additionalProperties": false,
    "required": [
        "name",
        "age"
    ]
}
JSON;

assert(json_encode($schema, JSON_PRETTY_PRINT) === $expected);
```

### Advanced schemas

All [attributes](https://github.com/bwaidelich/types/tree/main?tab=readme-ov-file#attributes) of the [wwwision/types](https://github.com/bwaidelich/types) package are supported in order to create sophisticated schemas.

<details>
<summary><b>Example: Complex composite object</b></summary>

```php
#[StringBased]
final class GivenName {
    private function __construct(public readonly string $value) {}
}

#[StringBased]
final class FamilyName {
    private function __construct(public readonly string $value) {}
}

final class FullName {
    public function __construct(
        public readonly GivenName $givenName,
        public readonly FamilyName $familyName,
    ) {}
}

#[Description('honorific title of a person')]
enum HonorificTitle
{
    #[Description('for men, regardless of marital status, who do not have another professional or academic title')]
    case MR;
    #[Description('for married women who do not have another professional or academic title')]
    case MRS;
    #[Description('for girls, unmarried women and married women who continue to use their maiden name')]
    case MISS;
    #[Description('for women, regardless of marital status or when marital status is unknown')]
    case MS;
    #[Description('for any other title that does not match the above')]
    case OTHER;
}

#[Description('A contact in the system')]
final class Contact {
    public function __construct(
        public readonly HonorificTitle $title,
        public readonly FullName $name,
        #[Description('Whether the contact is registered or not')]
        public bool $isRegistered = false,
    ) {}
}

$schema = JSONSchemaGenerator::fromClass(Contact::class);

$expected = <<<JSON
{
    "type": "object",
    "description": "A contact in the system",
    "properties": {
        "title": {
            "type": "string",
            "description": "honorific title of a person",
            "enum": [
                "MR",
                "MRS",
                "MISS",
                "MS",
                "OTHER"
            ]
        },
        "name": {
            "type": "object",
            "properties": {
                "givenName": {
                    "type": "string"
                },
                "familyName": {
                    "type": "string"
                }
            },
            "additionalProperties": false,
            "required": [
                "givenName",
                "familyName"
            ]
        },
        "isRegistered": {
            "type": "boolean",
            "description": "Whether the contact is registered or not"
        }
    },
    "additionalProperties": false,
    "required": [
        "title",
        "name"
    ]
}
JSON;

assert(json_encode($schema, JSON_PRETTY_PRINT) === $expected);
```

## Contribution

Contributions in the form of [issues](https://github.com/bwaidelich/types-jsonschema/issues) or [pull requests](https://github.com/bwaidelich/types-jsonschema/pulls) are highly appreciated

## License

See [LICENSE](./LICENSE)