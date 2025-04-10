<?php

declare(strict_types=1);

namespace Wwwision\TypesJsonSchema\Tests\PHPUnit;

require_once __DIR__ . '/Fixture/Fixture.php';

use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionParameter;
use Wwwision\JsonSchema\ArraySchema;
use Wwwision\JsonSchema\BooleanSchema;
use Wwwision\JsonSchema\Discriminator as JsonSchemaDiscriminator;
use Wwwision\JsonSchema\IntegerSchema;
use Wwwision\JsonSchema\NumberSchema;
use Wwwision\JsonSchema\ObjectProperties;
use Wwwision\JsonSchema\ObjectSchema;
use Wwwision\JsonSchema\OneOfSchema;
use Wwwision\JsonSchema\StringSchema;
use Wwwision\Types\Attributes\Description;
use Wwwision\Types\Parser;
use Wwwision\TypesJsonSchema\JsonSchemaGenerator;
use Wwwision\TypesJsonSchema\JsonSchemaGeneratorOptions;

#[CoversClass(ArraySchema::class)]
#[CoversClass(BooleanSchema::class)]
#[CoversClass(JsonSchemaDiscriminator::class)]
#[CoversClass(IntegerSchema::class)]
#[CoversClass(JsonSchemaGenerator::class)]
#[CoversClass(NumberSchema::class)]
#[CoversClass(ObjectProperties::class)]
#[CoversClass(ObjectSchema::class)]
#[CoversClass(StringSchema::class)]
final class JsonSchemaGeneratorTest extends TestCase
{
    private JsonSchemaGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new JsonSchemaGenerator();
    }


    public function test_fromClass_throws_exception_for_empty_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->generator->fromClass('');
    }

    public function test_fromClass_throws_exception_if_given_class_does_not_exist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->generator->fromClass('not-a-class');
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
        yield 'shape with interface property' => ['className' => Fixture\SomeShapeWithInterfaceProperty::class, 'expectedResult' => '{"additionalProperties":false,"properties":{"property":{"oneOf":[{"description":"First name of a person","maxLength":20,"minLength":3,"type":"string"},{"description":"Last name of a person","maxLength":20,"minLength":3,"type":"string"}]}},"required":["property"],"type":"object"}'];

        yield 'interface' => ['className' => Fixture\SomeInterface::class, 'expectedResult' => '{"oneOf":[{"description":"First name of a person","maxLength":20,"minLength":3,"type":"string"},{"description":"Last name of a person","maxLength":20,"minLength":3,"type":"string"},{"additionalProperties":false,"description":"First and last name of a person","properties":{"familyName":{"description":"Last name of a person","maxLength":20,"minLength":3,"type":"string"},"givenName":{"description":"Overridden given name description","maxLength":20,"minLength":3,"type":"string"}},"required":["givenName","familyName"],"type":"object"}]}'];
        yield 'interface with discriminator' => ['className' => Fixture\SomeInterfaceWithDiscriminator::class, 'expectedResult' => '{"oneOf":[{"description":"First name of a person","maxLength":20,"minLength":3,"type":"string"},{"description":"Last name of a person","maxLength":20,"minLength":3,"type":"string"}]}'];
    }

    #[DataProvider('fromClass_dataProvider')]
    public function test_fromClass(string $className, string $expectedResult): void
    {
        $schema = $this->generator->fromClass($className);
        self::assertJsonStringEqualsJsonString($expectedResult, json_encode($schema));
    }

    public static function fromClass_with_enabled_discriminator_dataProvider(): Generator
    {
        yield 'shape with discriminated union type' => ['className' => Fixture\SomeShapeWithDiscriminatedUnionType::class, 'expectedResult' => '{"additionalProperties":false,"properties":{"name":{"discriminator":{"mapping":{"f":"Wwwision\\\\TypesJsonSchema\\\\Tests\\\\PHPUnit\\\\Fixture\\\\FamilyName","g":"Wwwision\\\\TypesJsonSchema\\\\Tests\\\\PHPUnit\\\\Fixture\\\\GivenName"},"propertyName":"t"},"oneOf":[{"description":"First name of a person","maxLength":20,"minLength":3,"type":"string"},{"description":"Last name of a person","maxLength":20,"minLength":3,"type":"string"}]}},"required":["name"],"type":"object"}'];
        yield 'shape with interface property' => ['className' => Fixture\SomeShapeWithInterfaceProperty::class, 'expectedResult' => '{"additionalProperties":false,"properties":{"property":{"discriminator":{"mapping":{"family":"Wwwision\\\\TypesJsonSchema\\\\Tests\\\\PHPUnit\\\\Fixture\\\\FamilyName","given":"Wwwision\\\\TypesJsonSchema\\\\Tests\\\\PHPUnit\\\\Fixture\\\\GivenName"},"propertyName":"type"},"oneOf":[{"description":"First name of a person","maxLength":20,"minLength":3,"type":"string"},{"description":"Last name of a person","maxLength":20,"minLength":3,"type":"string"}]}},"required":["property"],"type":"object"}'];

        yield 'interface' => ['className' => Fixture\SomeInterface::class, 'expectedResult' => '{"oneOf":[{"description":"First name of a person","maxLength":20,"minLength":3,"type":"string"},{"description":"Last name of a person","maxLength":20,"minLength":3,"type":"string"},{"additionalProperties":false,"description":"First and last name of a person","properties":{"familyName":{"description":"Last name of a person","maxLength":20,"minLength":3,"type":"string"},"givenName":{"description":"Overridden given name description","maxLength":20,"minLength":3,"type":"string"}},"required":["givenName","familyName"],"type":"object"}]}'];
        yield 'interface with discriminator' => ['className' => Fixture\SomeInterfaceWithDiscriminator::class, 'expectedResult' => '{"discriminator":{"mapping":{"family":"Wwwision\\\\TypesJsonSchema\\\\Tests\\\\PHPUnit\\\\Fixture\\\\FamilyName","given":"Wwwision\\\\TypesJsonSchema\\\\Tests\\\\PHPUnit\\\\Fixture\\\\GivenName"},"propertyName":"type"},"oneOf":[{"description":"First name of a person","maxLength":20,"minLength":3,"type":"string"},{"description":"Last name of a person","maxLength":20,"minLength":3,"type":"string"}]}'];
    }

    #[DataProvider('fromClass_with_enabled_discriminator_dataProvider')]
    public function test_fromClassWithEnabledDiscriminiator(string $className, string $expectedResult): void
    {
        $generator = new JsonSchemaGenerator(JsonSchemaGeneratorOptions::create(includeDiscriminator: true));
        $schema = $generator->fromClass($className);
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
        $schema = $this->generator->fromReflectionParameter($reflectionParameter);
        self::assertJsonStringEqualsJsonString($expectedResult, json_encode($schema));
    }

    public function test_fromSchema_respects_interface_discriminators(): void
    {
        $shapeSchema = Parser::getSchema(Fixture\SomeInterfaceWithDiscriminator::class);
        $jsonSchema = $this->generator->fromSchema($shapeSchema);
        self::assertInstanceOf(OneOfSchema::class, $jsonSchema);
        self::assertNotNull($shapeSchema->discriminator);
        self::assertSame('type', $shapeSchema->discriminator->propertyName);
        self::assertSame(['given' => Fixture\GivenName::class, 'family' => Fixture\FamilyName::class], $shapeSchema->discriminator->mapping);
    }

    public function test_fromSchema_respects_union_type_discriminators(): void
    {
        $shapeSchema = Parser::getSchema(Fixture\SomeShapeWithDiscriminatedUnionType::class);
        $generator = new JsonSchemaGenerator(JsonSchemaGeneratorOptions::create(includeDiscriminator: true));
        $jsonSchema = $generator->fromSchema($shapeSchema);
        self::assertInstanceOf(ObjectSchema::class, $jsonSchema);
        self::assertNotNull($jsonSchema->properties);
        $oneOfSchema = iterator_to_array($jsonSchema->properties)['name'];
        self::assertInstanceOf(OneOfSchema::class, $oneOfSchema);
        self::assertNotNull($oneOfSchema->discriminator);
        self::assertSame('t', $oneOfSchema->discriminator->propertyName);
        self::assertSame(['g' => Fixture\GivenName::class, 'f' => Fixture\FamilyName::class], $oneOfSchema->discriminator->mapping);
    }

}
