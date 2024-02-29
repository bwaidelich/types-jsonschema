<?php

declare(strict_types=1);

namespace Wwwision\TypesJSONSchema\Types;

use Webmozart\Assert\Assert;

/**
 * @see https://json-schema.org/understanding-json-schema/reference/array
 */
final class ArraySchema implements SchemaWithDescription
{
    /**
     * @param array<mixed>|null $default
     * @param array<array<mixed>>|null $examples
     * @param array<mixed>|null $const
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
        public readonly Schema|false|null $items = null,
        public readonly ?ArrayItems $prefixItems = null,
        public readonly ArrayItems|false|Schema|null $unevaluatedItems = null,
        public readonly ?Schema $contains = null,
        public readonly ?int $minContains = null,
        public readonly ?int $maxContains = null,
        public readonly ?int $minItems = null,
        public readonly ?int $maxItems = null,
        public readonly ?bool $uniqueItems = null,
    ) {
        if ($this->contains === null) {
            Assert::null($this->minContains, '"minContains" can only be used if "contains" is defined');
            Assert::null($this->maxContains, '"maxContains" can only be used if "contains" is defined');
        }
    }

    /**
     * @param array<mixed>|null $default
     * @param array<array<mixed>>|null $examples
     * @param array<mixed>|null $const
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
        Schema|false|null $items = null,
        ?ArrayItems $prefixItems = null,
        ArrayItems|false|Schema|null $unevaluatedItems = null,
        ?Schema $contains = null,
        ?int $minContains = null,
        ?int $maxContains = null,
        ?int $minItems = null,
        ?int $maxItems = null,
        ?bool $uniqueItems = null,
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
            $items ?? $this->items,
            $prefixItems ?? $this->prefixItems,
            $unevaluatedItems ?? $this->unevaluatedItems,
            $contains ?? $this->contains,
            $minContains ?? $this->minContains,
            $maxContains ?? $this->maxContains,
            $minItems ?? $this->minItems,
            $maxItems ?? $this->maxItems,
            $uniqueItems ?? $this->uniqueItems,
        );
    }

    public function withDescription(string $description): self
    {
        return $this->with(description: $description);
    }

    public function jsonSerialize(): array
    {
        $array = [
            'type' => 'array',
            ...array_filter(get_object_vars($this), static fn ($v) => $v !== null),
        ];
        if ($this->comment) {
            unset($array['comment']);
            $array['$comment'] = $this->comment;
        }
        return $array;
    }
}
