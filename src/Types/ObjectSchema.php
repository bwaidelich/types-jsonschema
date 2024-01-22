<?php

declare(strict_types=1);

namespace Wwwision\TypesJSONSchema\Types;

use Webmozart\Assert\Assert;

/**
 * @see https://json-schema.org/understanding-json-schema/reference/object
 */
final class ObjectSchema implements Schema
{
    /**
     * @param array<mixed>|null $default
     * @param array<array<mixed>>|null $examples
     * @param array<mixed>|null $const
     * @param array<string>|null $required
     */
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly ?array $default = null,
        public readonly ?array $examples = null,
        public readonly ?bool $readOnly = null,
        public readonly ?bool $writeOnly = null,
        public readonly ?bool $deprecated = null,
        public readonly ?string $comment = null,
        public readonly ?array $const = null,
        public readonly ?ObjectProperties $properties = null,
        // TODO add patternProperties
        public readonly ?bool $additionalProperties = null,
        // TODO add unevaluatedProperties
        public readonly ?array $required = null,
        public readonly ?StringSchema $propertyNames = null,
        public readonly ?int $minProperties = null,
        public readonly ?int $maxProperties = null,
        // TODO add dependentRequired https://json-schema.org/understanding-json-schema/reference/conditionals
        // TODO add dependentSchemas https://json-schema.org/understanding-json-schema/reference/conditionals
        // TODO add if-then-else https://json-schema.org/understanding-json-schema/reference/conditionals#ifthenelse

    ) {
        if ($this->required !== null) {
            Assert::notEmpty($this->required);
        }
    }

    /**
     * @param array<mixed>|null $default
     * @param array<array<mixed>>|null $examples
     * @param array<mixed>|null $const
     * @param array<string>|null $required
     */
    public function with(
        ?string $title = null,
        ?string $description = null,
        ?array $default = null,
        ?array $examples = null,
        ?bool $readOnly = null,
        ?bool $writeOnly = null,
        ?bool $deprecated = null,
        ?string $comment = null,
        ?array $const = null,
        ?ObjectProperties $properties = null,
        ?bool $additionalProperties = null,
        ?array $required = null,
        ?StringSchema $propertyNames = null,
        ?int $minProperties = null,
        ?int $maxProperties = null,
    ): self {
        return new self(
            $title ?? $this->title,
            $description ?? $this->description,
            $default ?? $this->default,
            $examples ?? $this->examples,
            $readOnly ?? $this->readOnly,
            $writeOnly ?? $this->writeOnly,
            $deprecated ?? $this->deprecated,
            $comment ?? $this->comment,
            $const ?? $this->const,
            $properties ?? $this->properties,
            $additionalProperties ?? $this->additionalProperties,
            $required ?? $this->required,
            $propertyNames ?? $this->propertyNames,
            $minProperties ?? $this->minProperties,
            $maxProperties ?? $this->maxProperties,
        );
    }

    public function jsonSerialize(): array
    {
        $array = [
            'type' => 'object',
            ...array_filter(get_object_vars($this)),
        ];
        if ($this->comment) {
            unset($array['comment']);
            $array['$comment'] = $this->comment;
        }
        return $array;
    }
}