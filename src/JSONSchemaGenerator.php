<?php

declare(strict_types=1);

namespace Wwwision\TypesJSONSchema;

use InvalidArgumentException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionFunctionAbstract;
use ReflectionNamedType;
use ReflectionParameter;
use Webmozart\Assert\Assert;
use Wwwision\Types\Attributes\Description;
use Wwwision\Types\Parser;
use Wwwision\Types\Schema as Types;
use Wwwision\Types\Schema\LiteralBooleanSchema;
use Wwwision\Types\Schema\LiteralIntegerSchema;
use Wwwision\Types\Schema\LiteralStringSchema;
use Wwwision\TypesJSONSchema\Types\ArraySchema;
use Wwwision\TypesJSONSchema\Types\BooleanSchema;
use Wwwision\TypesJSONSchema\Types\IntegerSchema;
use Wwwision\TypesJSONSchema\Types\NumberSchema;
use Wwwision\TypesJSONSchema\Types\ObjectProperties;
use Wwwision\TypesJSONSchema\Types\ObjectSchema;
use Wwwision\TypesJSONSchema\Types\Schema;
use Wwwision\TypesJSONSchema\Types\SchemaWithDescription;
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

        $schema = self::fromSchema($parameterSchema);

        $description = self::getDescription($reflectionParameter);
        if ($description !== null && $schema instanceof SchemaWithDescription) {
            $schema = $schema->withDescription($description);
        }
        return $schema;
    }

    private static function reflectionTypeToSchema(ReflectionNamedType $reflectionType): Types\Schema
    {
        if ($reflectionType->isBuiltin()) {
            return match ($reflectionType->getName()) {
                'bool' => new LiteralBooleanSchema(null),
                'int' => new LiteralIntegerSchema(null),
                'string' => new LiteralStringSchema(null),
                'float' => new Types\LiteralFloatSchema(null),
                default => throw new InvalidArgumentException(sprintf('No support for type %s', $reflectionType->getName())),
            };
        }
        $typeClassName = $reflectionType->getName();
        Assert::classExists($typeClassName);
        return Parser::getSchema($typeClassName);
    }

    public static function fromSchema(Types\Schema $schema): Schema
    {
        return match ($schema::class) {
            Types\EnumSchema::class => self::fromEnumSchema($schema),
            Types\IntegerSchema::class => self::fromIntegerSchema($schema),
            Types\ListSchema::class => self::fromListSchema($schema),
            Types\LiteralBooleanSchema::class => self::fromLiteralBooleanSchema($schema),
            Types\LiteralIntegerSchema::class => self::fromLiteralIntegerSchema($schema),
            Types\LiteralStringSchema::class => self::fromLiteralStringSchema($schema),
            Types\LiteralFloatSchema::class => self::fromLiteralFloatSchema($schema),
            Types\ShapeSchema::class => self::fromShapeSchema($schema),
            Types\InterfaceSchema::class => self::fromInterfaceSchema($schema),
            Types\StringSchema::class => self::fromStringSchema($schema),
            Types\FloatSchema::class => self::fromFloatSchema($schema),
            default => throw new InvalidArgumentException(sprintf('Schema of type "%s" cannot be converted to JSON schema directly', get_debug_type($schema)), 1705424391),
        };
    }

    public static function fromEnumSchema(Types\EnumSchema $schema): StringSchema|IntegerSchema
    {
        if ($schema->getBackingType() === 'int') {
            return new IntegerSchema(
                description: $schema->getDescription(),
                enum: array_values(array_map(static fn (Types\EnumCaseSchema $caseSchema) => (int)$caseSchema->getValue(), $schema->caseSchemas)),
            );
        }
        return new StringSchema(
            description: $schema->getDescription(),
            enum: array_values(array_map(static fn (Types\EnumCaseSchema $caseSchema) => (string)$caseSchema->getValue(), $schema->caseSchemas)),
        );
    }

    public static function fromIntegerSchema(Types\IntegerSchema $schema): IntegerSchema
    {
        return new IntegerSchema(
            description: $schema->getDescription(),
            minimum: $schema->minimum,
            maximum: $schema->maximum,
        );
    }

    public static function fromListSchema(Types\ListSchema $schema): ArraySchema
    {
        return new ArraySchema(
            description: $schema->getDescription(),
            items: self::fromSchema($schema->itemSchema),
            minItems: $schema->minCount,
            maxItems: $schema->maxCount,
        );
    }

    public static function fromLiteralBooleanSchema(Types\LiteralBooleanSchema $schema): BooleanSchema
    {
        return new BooleanSchema(
            description: $schema->getDescription(),
        );
    }

    public static function fromLiteralIntegerSchema(Types\LiteralIntegerSchema $schema): IntegerSchema
    {
        return new IntegerSchema(
            description: $schema->getDescription(),
        );
    }

    public static function fromLiteralStringSchema(Types\LiteralStringSchema $schema): StringSchema
    {
        return new StringSchema(
            description: $schema->getDescription(),
        );
    }

    public static function fromLiteralFloatSchema(Types\LiteralFloatSchema $schema): NumberSchema
    {
        return new NumberSchema(
            description: $schema->getDescription(),
        );
    }

    public static function fromShapeSchema(Types\ShapeSchema $schema): ObjectSchema
    {
        return self::fromShapeOrInterfaceSchema($schema);
    }

    public static function fromInterfaceSchema(Types\InterfaceSchema $schema): ObjectSchema
    {
        return self::fromShapeOrInterfaceSchema($schema);
    }

    private static function fromShapeOrInterfaceSchema(Types\ShapeSchema|Types\InterfaceSchema $schema): ObjectSchema
    {
        if ($schema instanceof Types\InterfaceSchema) {
            $propertySchemas = [
                '__type' => new StringSchema(description: 'interface type discriminator'),
            ];
            $requiredProperties = ['__type'];
        } else {
            $propertySchemas = [];
            $requiredProperties = [];
        }
        foreach ($schema->propertySchemas as $propertyName => $propertySchema) {
            if ($propertySchema instanceof Types\OptionalSchema) {
                $propertySchema = $propertySchema->wrapped;
            } else {
                $requiredProperties[] = $propertyName;
            }
            $propertySchemas[$propertyName] = self::fromSchema($propertySchema);
            $overriddenPropertyDescription = $schema->overriddenPropertyDescription($propertyName);
            if ($overriddenPropertyDescription !== null && $propertySchemas[$propertyName] instanceof SchemaWithDescription) {
                $propertySchemas[$propertyName] = $propertySchemas[$propertyName]->withDescription($overriddenPropertyDescription);
            }
        }
        return new ObjectSchema(
            description: $schema->getDescription(),
            properties: ObjectProperties::create(...$propertySchemas),
            additionalProperties: false,
            required: $requiredProperties !== [] ? $requiredProperties : null,
        );
    }

    public static function fromStringSchema(Types\StringSchema $schema): StringSchema
    {
        return new StringSchema(
            description: $schema->getDescription(),
            minLength: $schema->minLength,
            maxLength: $schema->maxLength,
            format: $schema->format !== null ? instantiate(StringFormat::class, $schema->format->name) : null,
            pattern: $schema->pattern,
        );
    }

    public static function fromFloatSchema(Types\FloatSchema $schema): NumberSchema
    {
        return new NumberSchema(
            description: $schema->getDescription(),
            minimum: $schema->minimum,
            maximum: $schema->maximum,
        );
    }


    /**
     * @param ReflectionParameter|ReflectionClass<object>|ReflectionClassConstant|ReflectionFunctionAbstract $reflection
     * @return string|null
     */
    private static function getDescription(ReflectionParameter|ReflectionClass|ReflectionClassConstant|ReflectionFunctionAbstract $reflection): ?string
    {
        $descriptionAttributes = $reflection->getAttributes(Description::class, ReflectionAttribute::IS_INSTANCEOF);
        if (!isset($descriptionAttributes[0])) {
            return null;
        }
        /** @var Description $instance */
        $instance = $descriptionAttributes[0]->newInstance();
        return $instance->value;
    }
}
