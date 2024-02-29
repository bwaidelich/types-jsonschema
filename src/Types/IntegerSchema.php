<?php

declare(strict_types=1);

namespace Wwwision\TypesJSONSchema\Types;

/**
 * @see https://json-schema.org/understanding-json-schema/reference/numeric#integer
 */
final class IntegerSchema implements SchemaWithDescription
{
    /**
     * @param array<int>|null $examples
     * @param array<int>|null $enum
     */
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly ?int $default = null,
        public readonly ?array $examples = null,
        public readonly ?bool $readOnly = null,
        public readonly ?bool $writeOnly = null,
        public readonly ?bool $deprecated = null,
        public readonly ?string $comment = null,
        public readonly ?array $enum = null,
        public readonly ?int $const = null,
        public readonly ?int $multipleOf = null,
        public readonly ?int $minimum = null,
        public readonly ?bool $exclusiveMinimum = null,
        public readonly ?int $maximum = null,
        public readonly ?bool $exclusiveMaximum = null,
    ) {
    }

    /**
     * @param array<int>|null $examples
     * @param array<int>|null $enum
     */
    public function with(
        ?string $title = null,
        ?string $description = null,
        ?int $default = null,
        ?array $examples = null,
        ?bool $readOnly = null,
        ?bool $writeOnly = null,
        ?bool $deprecated = null,
        ?string $comment = null,
        ?array $enum = null,
        ?int $const = null,
        ?int $multipleOf = null,
        ?int $minimum = null,
        ?bool $exclusiveMinimum = null,
        ?int $maximum = null,
        ?bool $exclusiveMaximum = null,
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
            $enum ?? $this->enum,
            $const ?? $this->const,
            $multipleOf ?? $this->multipleOf,
            $minimum ?? $this->minimum,
            $exclusiveMinimum ?? $this->exclusiveMinimum,
            $maximum ?? $this->maximum,
            $exclusiveMaximum ?? $this->exclusiveMaximum,
        );
    }

    public function withDescription(string $description): self
    {
        return $this->with(description: $description);
    }

    public function jsonSerialize(): array
    {
        $array = [
            'type' => 'integer',
            ...array_filter(get_object_vars($this), static fn ($v) => $v !== null),
        ];
        if ($this->comment) {
            unset($array['comment']);
            $array['$comment'] = $this->comment;
        }
        return $array;
    }
}
