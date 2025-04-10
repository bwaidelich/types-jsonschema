<?php

declare(strict_types=1);

namespace Wwwision\TypesJsonSchema;

use Closure;
use InvalidArgumentException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionFunctionAbstract;
use ReflectionNamedType;
use ReflectionParameter;
use Webmozart\Assert\Assert;
use Wwwision\JsonSchema\ArraySchema;
use Wwwision\JsonSchema\BooleanSchema;
use Wwwision\JsonSchema\Discriminator;
use Wwwision\JsonSchema\IntegerSchema;
use Wwwision\JsonSchema\NumberSchema;
use Wwwision\JsonSchema\ObjectProperties;
use Wwwision\JsonSchema\ObjectSchema;
use Wwwision\JsonSchema\OneOfSchema;
use Wwwision\JsonSchema\Schema;
use Wwwision\JsonSchema\SchemaWithDescription;
use Wwwision\JsonSchema\StringFormat;
use Wwwision\JsonSchema\StringSchema;
use Wwwision\Types\Attributes\Description;
use Wwwision\Types\Parser;
use Wwwision\Types\Schema as Types;
use Wwwision\Types\Schema\LiteralBooleanSchema;
use Wwwision\Types\Schema\LiteralIntegerSchema;
use Wwwision\Types\Schema\LiteralStringSchema;

use function Wwwision\Types\instantiate;

/**
 * @phpstan-type SchemaCallback Closure(Types\Schema, Closure(Types\Schema): Schema): Schema
 */
final class JsonSchemaGenerator
{
    private readonly JsonSchemaGeneratorOptions $options;

    public function __construct(
        JsonSchemaGeneratorOptions|null $options = null,
    ) {
        $this->options = $options ?? JsonSchemaGeneratorOptions::create();
    }


    /**
     * @param class-string $className
     * @return Schema
     */
    public function fromClass(string $className): Schema
    {
        $schema = Parser::getSchema($className);
        return $this->fromSchema($schema);
    }

    public function fromReflectionParameter(ReflectionParameter $reflectionParameter): Schema
    {
        $parameterReflectionType = $reflectionParameter->getType();
        Assert::isInstanceOf($parameterReflectionType, ReflectionNamedType::class);
        $parameterSchema = $this->reflectionTypeToSchema($parameterReflectionType);

        $schema = $this->fromSchema($parameterSchema);

        $description = $this->getDescription($reflectionParameter);
        if ($description !== null && $schema instanceof SchemaWithDescription) {
            $schema = $schema->withDescription($description);
        }
        return $schema;
    }

    public function fromSchema(Types\Schema $schema): Schema
    {
        $middlewareChain = array_reduce(
            array_reverse($this->options->middlewares),
            static fn(callable $next, callable $middleware) => static fn(Types\Schema $schema): Schema => $middleware($schema, $next),
            fn(Types\Schema $schema): Schema => match ($schema::class) {
                Types\EnumSchema::class => $this->fromEnumSchema($schema),
                Types\IntegerSchema::class => $this->fromIntegerSchema($schema),
                Types\ListSchema::class => $this->fromListSchema($schema),
                Types\LiteralBooleanSchema::class => $this->fromLiteralBooleanSchema($schema),
                Types\LiteralIntegerSchema::class => $this->fromLiteralIntegerSchema($schema),
                Types\LiteralStringSchema::class => $this->fromLiteralStringSchema($schema),
                Types\LiteralFloatSchema::class => $this->fromLiteralFloatSchema($schema),
                Types\ShapeSchema::class => $this->fromShapeSchema($schema),
                Types\InterfaceSchema::class => $this->fromInterfaceSchema($schema),
                Types\StringSchema::class => $this->fromStringSchema($schema),
                Types\FloatSchema::class => $this->fromFloatSchema($schema),
                Types\OneOfSchema::class => $this->fromOneOfSchema($schema),
                default => throw new InvalidArgumentException(sprintf('Schema of type "%s" cannot be converted to JSON schema directly', get_debug_type($schema)), 1705424391),
            },
        );
        return $middlewareChain($schema);
    }

    private function reflectionTypeToSchema(ReflectionNamedType $reflectionType): Types\Schema
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

    private function fromEnumSchema(Types\EnumSchema $schema): StringSchema|IntegerSchema
    {
        if ($schema->getBackingType() === 'int') {
            return new IntegerSchema(
                description: $schema->getDescription(),
                enum: array_values(array_map(static fn(Types\EnumCaseSchema $caseSchema) => (int) $caseSchema->getValue(), $schema->caseSchemas)),
            );
        }
        return new StringSchema(
            description: $schema->getDescription(),
            enum: array_values(array_map(static fn(Types\EnumCaseSchema $caseSchema) => (string) $caseSchema->getValue(), $schema->caseSchemas)),
        );
    }

    private function fromIntegerSchema(Types\IntegerSchema $schema): IntegerSchema
    {

        return new IntegerSchema(
            description: $schema->getDescription(),
            minimum: $schema->minimum,
            maximum: $schema->maximum,
        );
    }

    private function fromListSchema(Types\ListSchema $schema): ArraySchema
    {
        return new ArraySchema(
            description: $schema->getDescription(),
            items: $this->fromSchema($schema->itemSchema),
            minItems: $schema->minCount,
            maxItems: $schema->maxCount,
        );
    }

    private function fromLiteralBooleanSchema(Types\LiteralBooleanSchema $schema): BooleanSchema
    {
        return new BooleanSchema(
            description: $schema->getDescription(),
        );
    }

    private function fromLiteralIntegerSchema(Types\LiteralIntegerSchema $schema): IntegerSchema
    {
        return new IntegerSchema(
            description: $schema->getDescription(),
        );
    }

    private function fromLiteralStringSchema(Types\LiteralStringSchema $schema): StringSchema
    {
        return new StringSchema(
            description: $schema->getDescription(),
        );
    }

    private function fromLiteralFloatSchema(Types\LiteralFloatSchema $schema): NumberSchema
    {
        return new NumberSchema(
            description: $schema->getDescription(),
        );
    }

    private function fromShapeSchema(Types\ShapeSchema $schema): ObjectSchema
    {
        $propertySchemas = [];
        $requiredProperties = [];
        foreach ($schema->propertySchemas as $propertyName => $propertySchema) {
            if ($propertySchema instanceof Types\OptionalSchema) {
                $propertySchema = $propertySchema->wrapped;
            } else {
                $requiredProperties[] = $propertyName;
            }
            $propertySchemas[$propertyName] = $this->fromSchema($propertySchema);
            $overriddenPropertyDescription = $schema->overriddenPropertyDescription($propertyName);
            if ($overriddenPropertyDescription !== null && $propertySchemas[$propertyName] instanceof SchemaWithDescription) {
                $propertySchemas[$propertyName] = $propertySchemas[$propertyName]->withDescription($overriddenPropertyDescription);
            }
        }
        $objectSchema = new ObjectSchema(
            description: $schema->getDescription(),
            properties: ObjectProperties::create(...$propertySchemas),
            additionalProperties: false,
            required: $requiredProperties !== [] ? $requiredProperties : null,
        );
        return $objectSchema;
    }

    private function fromInterfaceSchema(Types\InterfaceSchema $schema): OneOfSchema
    {
        $result = OneOfSchema::create(
            ...array_map($this->fromSchema(...), $schema->implementationSchemas()),
        );
        if ($this->options->includeDiscriminator && $schema->discriminator !== null) {
            $result = $result->withDiscriminator(new Discriminator($schema->discriminator->propertyName, $schema->discriminator->mapping));
        }
        return $result;
    }

    private function fromOneOfSchema(Types\OneOfSchema $schema): OneOfSchema
    {
        $result = OneOfSchema::create(
            ...array_map($this->fromSchema(...), $schema->subSchemas),
        );
        if ($this->options->includeDiscriminator && $schema->discriminator !== null) {
            $result = $result->withDiscriminator(new Discriminator($schema->discriminator->propertyName, $schema->discriminator->mapping));
        }
        return $result;
    }

    private function fromStringSchema(Types\StringSchema $schema): StringSchema
    {
        return new StringSchema(
            description: $schema->getDescription(),
            minLength: $schema->minLength,
            maxLength: $schema->maxLength,
            format: $schema->format !== null ? instantiate(StringFormat::class, $schema->format->name) : null,
            pattern: $schema->pattern,
        );
    }

    private function fromFloatSchema(Types\FloatSchema $schema): NumberSchema
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
    private function getDescription(ReflectionParameter|ReflectionClass|ReflectionClassConstant|ReflectionFunctionAbstract $reflection): null|string
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
