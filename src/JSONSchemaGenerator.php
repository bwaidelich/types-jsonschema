<?php

declare(strict_types=1);

namespace Wwwision\TypesJSONSchema;

use InvalidArgumentException;
use ReflectionNamedType;
use ReflectionParameter;
use Webmozart\Assert\Assert;
use Wwwision\Types\Parser;
use Wwwision\Types\Schema as Base;
use Wwwision\Types\Schema\LiteralBooleanSchema;
use Wwwision\Types\Schema\LiteralIntegerSchema;
use Wwwision\Types\Schema\LiteralStringSchema;
use Wwwision\TypesJSONSchema\Types\ArraySchema;
use Wwwision\TypesJSONSchema\Types\BooleanSchema;
use Wwwision\TypesJSONSchema\Types\IntegerSchema;
use Wwwision\TypesJSONSchema\Types\ObjectProperties;
use Wwwision\TypesJSONSchema\Types\ObjectSchema;
use Wwwision\TypesJSONSchema\Types\Schema;
use Wwwision\TypesJSONSchema\Types\StringFormat;
use Wwwision\TypesJSONSchema\Types\StringSchema;
use function Wwwision\Types\instantiate;

final class JSONSchemaGenerator
{
    /**
     * @param class-string $className
     * @return Schema
     */
    public static function fromClass(string $className): Schema
    {
        $schema = Parser::getSchema($className);
        return self::fromSchema($schema);
    }

    public static function fromReflectionParameter(ReflectionParameter $reflectionParameter): Schema
    {
        $parameterReflectionType = $reflectionParameter->getType();
        Assert::isInstanceOf($parameterReflectionType, ReflectionNamedType::class);
        $parameterSchema = self::reflectionTypeToSchema($parameterReflectionType);
        return self::fromSchema($parameterSchema);
    }

    private static function reflectionTypeToSchema(ReflectionNamedType $reflectionType): Base\Schema
    {
        if ($reflectionType->isBuiltin()) {
            return match ($reflectionType->getName()) {
                'bool' => new LiteralBooleanSchema(null),
                'int' => new LiteralIntegerSchema(null),
                'string' => new LiteralStringSchema(null),
                default => throw new InvalidArgumentException(sprintf('No support for type %s', $reflectionType->getName())),
            };
        }
        $typeClassName = $reflectionType->getName();
        Assert::classExists($typeClassName);
        return Parser::getSchema($typeClassName);
    }

    public static function fromSchema(Base\Schema $schema): Schema
    {
        return match ($schema::class) {
            Base\EnumSchema::class => self::fromEnumSchema($schema),
            Base\IntegerSchema::class => self::fromIntegerSchema($schema),
            Base\ListSchema::class => self::fromListSchema($schema),
            Base\LiteralBooleanSchema::class => self::fromLiteralBooleanSchema($schema),
            Base\LiteralIntegerSchema::class => self::fromLiteralIntegerSchema($schema),
            Base\LiteralStringSchema::class => self::fromLiteralStringSchema($schema),
            Base\ShapeSchema::class => self::fromShapeSchema($schema),
            Base\StringSchema::class => self::fromStringSchema($schema),
            default => throw new InvalidArgumentException(sprintf('Schema of type "%s" cannot be converted to JSON schema directly', get_debug_type($schema)), 1705424391),
        };
    }

    public static function fromEnumSchema(Base\EnumSchema $schema): StringSchema
    {
        return new StringSchema(
            description: $schema->getDescription(),
            enum: array_values(array_map(static fn (Base\EnumCaseSchema $caseSchema) => $caseSchema->getName(), $schema->caseSchemas)),
        );
    }

    public static function fromIntegerSchema(Base\IntegerSchema $schema): IntegerSchema
    {
        return new IntegerSchema(
            description: $schema->getDescription(),
            minimum: $schema->minimum,
            maximum: $schema->maximum,
        );
    }

    public static function fromListSchema(Base\ListSchema $schema): ArraySchema
    {
        return new ArraySchema(
            description: $schema->getDescription(),
            items: self::fromSchema($schema->itemSchema),
            minItems: $schema->minCount,
            maxItems: $schema->maxCount,
        );
    }

    public static function fromLiteralBooleanSchema(Base\LiteralBooleanSchema $schema): BooleanSchema
    {
        return new BooleanSchema(
            description: $schema->getDescription(),
        );
    }

    public static function fromLiteralIntegerSchema(Base\LiteralIntegerSchema $schema): IntegerSchema
    {
        return new IntegerSchema(
            description: $schema->getDescription(),
        );
    }

    public static function fromLiteralStringSchema(Base\LiteralStringSchema $schema): StringSchema
    {
        return new StringSchema(
            description: $schema->getDescription(),
        );
    }

    public static function fromShapeSchema(Base\ShapeSchema $schema): ObjectSchema
    {
        $propertySchemas = [];
        $requiredProperties = [];
        foreach ($schema->propertySchemas as $propertyName => $propertySchema) {
            if ($propertySchema instanceof Base\OptionalSchema) {
                $propertySchema = $propertySchema->wrapped;
            } else {
                $requiredProperties[] = $propertyName;
            }
            // TODO consider $schema->overriddenPropertyDescription($propertyName)
            $propertySchemas[$propertyName] = self::fromSchema($propertySchema);
        }
        return new ObjectSchema(
            description: $schema->getDescription(),
            properties: ObjectProperties::create(...$propertySchemas),
            additionalProperties: false,
            required: $requiredProperties !== [] ? $requiredProperties : null,

        );
    }

    public static function fromStringSchema(Base\StringSchema $schema): StringSchema
    {
        return new StringSchema(
            description: $schema->getDescription(),
            minLength: $schema->minLength,
            maxLength: $schema->maxLength,
            format: $schema->format !== null ? instantiate(StringFormat::class, $schema->format->name) : null,
        );
    }

}