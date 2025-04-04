<?php

declare(strict_types=1);

namespace Wwwision\TypesJSONSchema\Tests\PHPUnit;

require_once __DIR__ . '/Fixture/Fixture.php';

use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionParameter;
use Wwwision\Types\Attributes\Description;
use Wwwision\Types\Parser;
use Wwwision\TypesJSONSchema\JSONSchemaGenerator;
use Wwwision\TypesJSONSchema\Types\ArraySchema;
use Wwwision\TypesJSONSchema\Types\BooleanSchema;
use Wwwision\TypesJSONSchema\Types\Discriminator as JsonSchemaDiscriminator;
use Wwwision\TypesJSONSchema\Types\IntegerSchema;
use Wwwision\TypesJSONSchema\Types\NumberSchema;
use Wwwision\TypesJSONSchema\Types\ObjectProperties;
use Wwwision\TypesJSONSchema\Types\ObjectSchema;
use Wwwision\TypesJSONSchema\Types\OneOfSchema;
use Wwwision\TypesJSONSchema\Types\StringSchema;
use Wwwision\TypesJSONSchema\Tests\PHPUnit\Fixture;

#[CoversClass(ArraySchema::class)]
#[CoversClass(BooleanSchema::class)]
#[CoversClass(JsonSchemaDiscriminator::class)]
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
        yield 'enum' => ['className' => Fixture\Title::class, 'expectedResult' => '{"description": "honorific title of a person","enum": ["MR","MRS","MISS","MS","OTHER"],"type": "string"}'];
        yield 'int backed enum' => ['className' => Fixture\Number::class, 'expectedResult' => '{"type":"integer","description":"A number","enum":[2,4,5]}'];
        yield 'string backed enum' => ['className' => Fixture\RomanNumber::class, 'expectedResult' => '{"type":"string","enum":["1","2","3","4"]}'];

        yield 'integer based object' => ['className' => Fixture\Age::class, 'expectedResult' => '{"type":"integer","description":"The age of a person in years","minimum":1,"maximum":120}'];
        yield 'list object' => ['className' => Fixture\FullNames::class, 'expectedResult' => '{"type":"array","items":{"type":"object","description":"First and last name of a person","properties":{"givenName":{"type":"string","description":"Overridden given name description","minLength":3,"maxLength":20},"familyName":{"type":"string","description":"Last name of a person","minLength":3,"maxLength":20}},"additionalProperties":false,"required":["givenName","familyName"]},"minItems":2,"maxItems":5}'];
        yield 'shape object' => ['className' => Fixture\FullName::class, 'expectedResult' => '{"type":"object","description":"First and last name of a person","properties":{"givenName":{"type":"string","description":"Overridden given name description","minLength":3,"maxLength":20},"familyName":{"type":"string","description":"Last name of a person","minLength":3,"maxLength":20}},"additionalProperties":false,"required":["givenName","familyName"]}'];
        yield 'shape object with optional properties' => ['className' => Fixture\ShapeWithOptionalTypes::class, 'expectedResult' => '{"type":"object","properties":{"stringBased":{"type":"string","description":"Last name of a person","minLength":3,"maxLength":20},"optionalStringBased":{"type":"string","description":"Last name of a person","minLength":3,"maxLength":20},"optionalInt":{"type":"integer","description":"Some description"},"optionalBool":{"type":"boolean"},"optionalString":{"type":"string"}},"additionalProperties":false,"required":["stringBased"]}'];

        yield 'string based object' => ['className' => Fixture\GivenName::class, 'expectedResult' => '{"type":"string","description":"First name of a person","minLength":3,"maxLength":20}'];
        yield 'string based object with format' => ['className' => Fixture\EmailAddress::class, 'expectedResult' => '{"type":"string","format":"email"}'];
        yield 'string based object with pattern' => ['className' => Fixture\NotMagic::class, 'expectedResult' => '{"type":"string","pattern":"^(?!magic).*"}'];

        yield 'shape with bool' => ['className' => Fixture\ShapeWithBool::class, 'expectedResult' => '{"type":"object","properties":{"value":{"type":"boolean","description":"Description for literal bool"}},"additionalProperties":false,"required":["value"]}'];
        yield 'shape with int' => ['className' => Fixture\ShapeWithInt::class, 'expectedResult' => '{"type":"object","properties":{"value":{"type":"integer","description":"Description for literal int"}},"additionalProperties":false,"required":["value"]}'];
        yield 'shape with string' => ['className' => Fixture\ShapeWithString::class, 'expectedResult' => '{"type":"object","properties":{"value":{"type":"string","description":"Description for literal string"}},"additionalProperties":false,"required":["value"]}'];
        yield 'shape with floats' => ['className' => Fixture\GeoCoordinates::class, 'expectedResult' => '{"type":"object","properties":{"longitude":{"type":"number","minimum":-180,"maximum":180.5},"latitude":{"type":"number","minimum":-90,"maximum":90}},"additionalProperties":false,"required":["longitude","latitude"]}'];
        yield 'shape with discriminated union type' => ['className' => Fixture\SomeShapeWithDiscriminatedUnionType::class, 'expectedResult' => '{"additionalProperties":false,"properties":{"name":{"oneOf":[{"description":"First name of a person","maxLength":20,"minLength":3,"type":"string"},{"description":"Last name of a person","maxLength":20,"minLength":3,"type":"string"}]}},"required":["name"],"type":"object"}'];

        yield 'interface' => ['className' => Fixture\SomeInterface::class, 'expectedResult' => '{"type":"object","description":"SomeInterface description","properties":{"__type":{"type":"string","description":"interface type discriminator"},"someMethod":{"type":"string","description":"Custom description for \"someMethod\""},"someOtherMethod":{"type":"string","description":"Custom description for \"someOtherMethod\"","minLength":3,"maxLength":20}},"additionalProperties":false,"required":["__type","someMethod"]}'];
        yield 'interface with discriminator' => ['className' => Fixture\SomeInterfaceWithDiscriminator::class, 'expectedResult' => '{"additionalProperties":false,"properties":{"type":{"description":"interface type discriminator","type":"string"}},"required":["type"],"type":"object"}'];
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

        yield 'string based' => ['reflectionParameter' => new ReflectionParameter(fn(Fixture\GivenName $param) => null, 0), 'expectedResult' => '{"type":"string","description":"First name of a person","minLength":3,"maxLength":20}'];
        yield 'string based with custom description' => ['reflectionParameter' => new ReflectionParameter(fn(#[Description('some overridden description')] Fixture\GivenName $param) => null, 0), 'expectedResult' => '{"type":"string","description":"some overridden description","minLength":3,"maxLength":20}'];

        yield 'string with custom description' => ['reflectionParameter' => new ReflectionParameter(fn(#[Description('some description')] string $param) => null, 0), 'expectedResult' => '{"type":"string","description":"some description"}'];
    }

    #[DataProvider('fromReflectionParameter_dataProvider')]
    public function test_fromReflectionParameter(ReflectionParameter $reflectionParameter, string $expectedResult): void
    {
        $schema = JSONSchemaGenerator::fromReflectionParameter($reflectionParameter);
        self::assertJsonStringEqualsJsonString($expectedResult, json_encode($schema));
    }

    public function test_fromSchema_respects_interface_discriminators(): void
    {
        $shapeSchema = Parser::getSchema(Fixture\SomeInterfaceWithDiscriminator::class);
        $jsonSchema = JSONSchemaGenerator::fromSchema($shapeSchema);
        self::assertInstanceOf(ObjectSchema::class, $jsonSchema);
        self::assertNotNull($shapeSchema->discriminator);
        self::assertSame('type', $shapeSchema->discriminator->propertyName);
        self::assertSame(['given' => Fixture\GivenName::class, 'family' => Fixture\FamilyName::class], $shapeSchema->discriminator->mapping);
    }

    public function test_fromSchema_respects_union_type_discriminators(): void
    {
        $shapeSchema = Parser::getSchema(Fixture\SomeShapeWithDiscriminatedUnionType::class);
        $jsonSchema = JSONSchemaGenerator::fromSchema($shapeSchema);
        self::assertInstanceOf(ObjectSchema::class, $jsonSchema);
        self::assertNotNull($jsonSchema->properties);
        $oneOfSchema = iterator_to_array($jsonSchema->properties)['name'];
        self::assertInstanceOf(OneOfSchema::class, $oneOfSchema);
        self::assertNotNull($oneOfSchema->discriminator);
        self::assertSame('t', $oneOfSchema->discriminator->propertyName);
        self::assertSame(['g' => Fixture\GivenName::class, 'f' => Fixture\FamilyName::class], $oneOfSchema->discriminator->mapping);
    }

}
