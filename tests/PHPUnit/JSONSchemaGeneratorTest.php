<?php

declare(strict_types=1);

namespace Wwwision\TypesJSONSchema\Tests\PHPUnit;

use ArrayIterator;
use DateTimeImmutable;
use Generator;
use InvalidArgumentException;
use IteratorAggregate;
use JsonSerializable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionParameter;
use stdClass;
use Traversable;
use Wwwision\Types\Attributes\Description;
use Wwwision\Types\Attributes\FloatBased;
use Wwwision\Types\Attributes\IntegerBased;
use Wwwision\Types\Attributes\ListBased;
use Wwwision\Types\Attributes\StringBased;
use Wwwision\Types\Exception\CoerceException;
use Wwwision\Types\Parser;
use Wwwision\Types\Schema\StringTypeFormat;
use Wwwision\TypesJSONSchema\JSONSchemaGenerator;
use Wwwision\TypesJSONSchema\Types\ArraySchema;
use Wwwision\TypesJSONSchema\Types\BooleanSchema;
use Wwwision\TypesJSONSchema\Types\IntegerSchema;
use Wwwision\TypesJSONSchema\Types\NumberSchema;
use Wwwision\TypesJSONSchema\Types\ObjectProperties;
use Wwwision\TypesJSONSchema\Types\ObjectSchema;
use Wwwision\TypesJSONSchema\Types\StringSchema;

use function Wwwision\Types\instantiate;

#[CoversClass(ArraySchema::class)]
#[CoversClass(BooleanSchema::class)]
#[CoversClass(IntegerSchema::class)]
#[CoversClass(JSONSchemaGenerator::class)]
#[CoversClass(NumberSchema::class)]
#[CoversClass(ObjectProperties::class)]
#[CoversClass(ObjectSchema::class)]
#[CoversClass(StringSchema::class)]
final class JSONSchemaGeneratorTest extends TestCase
{
    public function test_fromClass_throws_exception_for_empty_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        JSONSchemaGenerator::fromClass('');
    }

    public function test_fromClass_throws_exception_if_given_class_does_not_exist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        JSONSchemaGenerator::fromClass('not-a-class');
    }

    public static function fromClass_dataProvider(): Generator
    {
        yield 'enum' => ['className' => Title::class, 'expectedResult' => '{"description": "honorific title of a person","enum": ["MR","MRS","MISS","MS","OTHER"],"type": "string"}'];
        yield 'int backed enum' => ['className' => Number::class, 'expectedResult' => '{"type":"integer","description":"A number","enum":[2,4,5]}'];
        yield 'string backed enum' => ['className' => RomanNumber::class, 'expectedResult' => '{"type":"string","enum":["1","2","3","4"]}'];

        yield 'integer based object' => ['className' => Age::class, 'expectedResult' => '{"type":"integer","description":"The age of a person in years","minimum":1,"maximum":120}'];
        yield 'list object' => ['className' => FullNames::class, 'expectedResult' => '{"type":"array","items":{"type":"object","description":"First and last name of a person","properties":{"givenName":{"type":"string","description":"Overridden given name description","minLength":3,"maxLength":20},"familyName":{"type":"string","description":"Last name of a person","minLength":3,"maxLength":20}},"additionalProperties":false,"required":["givenName","familyName"]},"minItems":2,"maxItems":5}'];
        yield 'shape object' => ['className' => FullName::class, 'expectedResult' => '{"type":"object","description":"First and last name of a person","properties":{"givenName":{"type":"string","description":"Overridden given name description","minLength":3,"maxLength":20},"familyName":{"type":"string","description":"Last name of a person","minLength":3,"maxLength":20}},"additionalProperties":false,"required":["givenName","familyName"]}'];
        yield 'shape object with optional properties' => ['className' => ShapeWithOptionalTypes::class, 'expectedResult' => '{"type":"object","properties":{"stringBased":{"type":"string","description":"Last name of a person","minLength":3,"maxLength":20},"optionalStringBased":{"type":"string","description":"Last name of a person","minLength":3,"maxLength":20},"optionalInt":{"type":"integer","description":"Some description"},"optionalBool":{"type":"boolean"},"optionalString":{"type":"string"}},"additionalProperties":false,"required":["stringBased"]}'];

        yield 'string based object' => ['className' => GivenName::class, 'expectedResult' => '{"type":"string","description":"First name of a person","minLength":3,"maxLength":20}'];
        yield 'string based object with format' => ['className' => EmailAddress::class, 'expectedResult' => '{"type":"string","format":"email"}'];
        yield 'string based object with pattern' => ['className' => NotMagic::class, 'expectedResult' => '{"type":"string","pattern":"^(?!magic).*"}'];

        yield 'shape with bool' => ['className' => ShapeWithBool::class, 'expectedResult' => '{"type":"object","properties":{"value":{"type":"boolean","description":"Description for literal bool"}},"additionalProperties":false,"required":["value"]}'];
        yield 'shape with int' => ['className' => ShapeWithInt::class, 'expectedResult' => '{"type":"object","properties":{"value":{"type":"integer","description":"Description for literal int"}},"additionalProperties":false,"required":["value"]}'];
        yield 'shape with string' => ['className' => ShapeWithString::class, 'expectedResult' => '{"type":"object","properties":{"value":{"type":"string","description":"Description for literal string"}},"additionalProperties":false,"required":["value"]}'];
        yield 'shape with floats' => ['className' => GeoCoordinates::class, 'expectedResult' => '{"type":"object","properties":{"longitude":{"type":"number","minimum":-180,"maximum":180.5},"latitude":{"type":"number","minimum":-90,"maximum":90}},"additionalProperties":false,"required":["longitude","latitude"]}'];

        yield 'interface' => ['className' => SomeInterface::class, 'expectedResult' => '{"type":"object","description":"SomeInterface description","properties":{"__type":{"type":"string","description":"interface type discriminator"},"someMethod":{"type":"string","description":"Custom description for \"someMethod\""},"someOtherMethod":{"type":"string","description":"Custom description for \"someOtherMethod\"","minLength":3,"maxLength":20}},"additionalProperties":false,"required":["__type","someMethod"]}'];
    }

    #[DataProvider('fromClass_dataProvider')]
    public function test_fromClass(string $className, string $expectedResult): void
    {
        $schema = JSONSchemaGenerator::fromClass($className);
        self::assertJsonStringEqualsJsonString($expectedResult, json_encode($schema));
    }

    public static function fromReflectionParameter_dataProvider(): Generator
    {
        yield 'bool' => ['reflectionParameter' => new ReflectionParameter(fn(bool $param) => null, 0), 'expectedResult' => '{"type":"boolean"}'];
        yield 'int' => ['reflectionParameter' => new ReflectionParameter(fn(int $param) => null, 0), 'expectedResult' => '{"type":"integer"}'];
        yield 'string' => ['reflectionParameter' => new ReflectionParameter(fn(string $param) => null, 0), 'expectedResult' => '{"type":"string"}'];
        yield 'float' => ['reflectionParameter' => new ReflectionParameter(fn(float $param) => null, 0), 'expectedResult' => '{"type":"number"}'];

        yield 'string based' => ['reflectionParameter' => new ReflectionParameter(fn(GivenName $param) => null, 0), 'expectedResult' => '{"type":"string","description":"First name of a person","minLength":3,"maxLength":20}'];
        yield 'string based with custom description' => ['reflectionParameter' => new ReflectionParameter(fn(#[Description('some overridden description')] GivenName $param) => null, 0), 'expectedResult' => '{"type":"string","description":"some overridden description","minLength":3,"maxLength":20}'];

        yield 'string with custom description' => ['reflectionParameter' => new ReflectionParameter(fn(#[Description('some description')] string $param) => null, 0), 'expectedResult' => '{"type":"string","description":"some description"}'];
    }

    #[DataProvider('fromReflectionParameter_dataProvider')]
    public function test_fromReflectionParameter(ReflectionParameter $reflectionParameter, string $expectedResult): void
    {
        $schema = JSONSchemaGenerator::fromReflectionParameter($reflectionParameter);
        self::assertJsonStringEqualsJsonString($expectedResult, json_encode($schema));
    }

}

#[StringBased(minLength: 3, maxLength: 20)]
#[Description('First name of a person')]
final class GivenName implements SomeInterface, JsonSerializable
{
    private function __construct(public readonly string $value) {}

    public function someMethod(): string
    {
        return 'bar';
    }

    public function someOtherMethod(): FamilyName
    {
        return instantiate(self::class, $this->value);
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}

#[StringBased(minLength: 3, maxLength: 20)]
#[Description('Last name of a person')]
final class FamilyName implements JsonSerializable, SomeInterface
{
    private function __construct(public readonly string $value) {}

    public function someMethod(): string
    {
        return 'bar';
    }

    public function someOtherMethod(): FamilyName
    {
        return instantiate(self::class, $this->value);
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}

#[IntegerBased(minimum: 1, maximum: 120)]
#[Description('The age of a person in years')]
final class Age
{
    private function __construct(public readonly int $value) {}
}


#[Description('First and last name of a person')]
final class FullName implements SomeInterface
{
    public function __construct(
        #[Description('Overridden given name description')]
        public readonly GivenName $givenName,
        public readonly FamilyName $familyName,
    ) {}

    public function someMethod(): string
    {
        return 'baz';
    }

    public function someOtherMethod(): FamilyName
    {
        return $this->familyName;
    }
}

/** @implements IteratorAggregate<FullName> */
#[ListBased(itemClassName: FullName::class, minCount: 2, maxCount: 5)]
final class FullNames implements IteratorAggregate
{
    private array $fullNames;

    /** @param array<FullName> $fullNames */
    private function __construct(FullName... $fullNames)
    {
        $this->fullNames = $fullNames;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->fullNames);
    }
}

#[ListBased(itemClassName: GivenName::class, maxCount: 4)]
final class GivenNames implements IteratorAggregate, JsonSerializable
{
    /** @param array<GivenName> $givenNames */
    private function __construct(private readonly array $givenNames) {}

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->givenNames);
    }

    public function jsonSerialize(): array
    {
        return $this->givenNames;
    }
}

#[ListBased(itemClassName: Uri::class)]
final class UriMap implements IteratorAggregate, JsonSerializable
{
    private function __construct(private readonly array $entries)
    {
        if (array_keys($entries) !== array_filter(\array_keys($entries), '\is_string')) {
            throw CoerceException::custom('Expected associative array with string keys', $entries, Parser::getSchema(self::class), );
        }
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->entries);
    }

    public function jsonSerialize(): array
    {
        return $this->entries;
    }

}

#[StringBased(pattern: '^(?!magic).*')]
final class NotMagic
{
    private function __construct(public readonly string $value) {}
}

#[StringBased(format: StringTypeFormat::email)]
final class EmailAddress
{
    private function __construct(public readonly string $value) {}
}

#[StringBased(format: StringTypeFormat::uri)]
final class Uri implements JsonSerializable
{
    private function __construct(public readonly string $value) {}

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}

#[StringBased(format: StringTypeFormat::date)]
final class Date
{
    private function __construct(public readonly string $value)
    {
        $now = new DateTimeImmutable();
        if (DateTimeImmutable::createFromFormat('Y-m-d', $this->value) > $now) {
            throw CoerceException::custom('Future dates are not allowed', $value, Parser::getSchema(self::class), ['some' => 'param']);
        }
    }
}

#[StringBased(format: StringTypeFormat::date_time)]
final class DateTime
{
    private function __construct(public readonly string $value) {}
}

#[StringBased(format: StringTypeFormat::uuid)]
final class Uuid
{
    private function __construct(public readonly string $value) {}
}

#[Description('honorific title of a person')]
enum Title
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

#[Description('A number')]
enum Number: int
{
    #[Description('The number 2')]
    case TWO = 2;
    case FOUR = 4;
    case FIVE = 5;
}

enum RomanNumber: string
{
    case I = '1';
    #[Description('random description')]
    case II = '2';
    case III = '3';
    case IV = '4';
}

final class NestedShape
{
    public function __construct(
        public readonly ShapeWithOptionalTypes $shapeWithOptionalTypes,
        public readonly ShapeWithBool $shapeWithBool,
    ) {}
}

final class ShapeWithOptionalTypes
{
    public function __construct(
        public readonly FamilyName $stringBased,
        public readonly ?FamilyName $optionalStringBased = null,
        #[Description('Some description')]
        public readonly ?int $optionalInt = null,
        public readonly ?bool $optionalBool = false,
        public readonly ?string $optionalString = null,
    ) {}
}

final class ShapeWithInvalidObjectProperty
{
    public function __construct(
        public readonly stdClass $someProperty,
    ) {}
}

final class ShapeWithBool
{
    private function __construct(
        #[Description('Description for literal bool')]
        public readonly bool $value,
    ) {}
}

final class ShapeWithInt
{
    private function __construct(
        #[Description('Description for literal int')]
        public readonly int $value,
    ) {}
}

final class ShapeWithString
{
    private function __construct(
        #[Description('Description for literal string')]
        public readonly string $value,
    ) {}
}

#[Description('SomeInterface description')]
interface SomeInterface
{
    #[Description('Custom description for "someMethod"')]
    public function someMethod(): string;
    #[Description('Custom description for "someOtherMethod"')]
    public function someOtherMethod(): ?FamilyName;
}

#[FloatBased(minimum: -180.0, maximum: 180.5)]
final class Longitude
{
    private function __construct(
        public readonly float $value,
    ) {}
}

#[FloatBased(minimum: -90, maximum: 90)]
final class Latitude
{
    private function __construct(
        public readonly float $value,
    ) {}
}

final class GeoCoordinates
{
    public function __construct(
        public readonly Longitude $longitude,
        public readonly Latitude $latitude,
    ) {}
}


interface SomeInvalidInterface
{
    public function methodWithParameters(string|null $param = null): string;
}

#[StringBased(minLength: 10, maxLength: 2, pattern: '^foo$', format: StringTypeFormat::email)]
final class ImpossibleString
{
    private function __construct(public readonly string $value) {}
}

#[IntegerBased(minimum: 10, maximum: 2)]
final class ImpossibleInt
{
    private function __construct(public readonly string $value) {}
}

#[FloatBased(minimum: 10.23, maximum: 2.45)]
final class ImpossibleFloat
{
    private function __construct(public readonly string $value) {}
}

#[ListBased(itemClassName: GivenName::class, minCount: 10, maxCount: 2)]
final class ImpossibleList
{
    private function __construct(private readonly array $items) {}
}
