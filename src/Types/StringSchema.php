<?php

declare(strict_types=1);

namespace Wwwision\TypesJSONSchema\Types;

/**
 * @see https://json-schema.org/understanding-json-schema/reference/string
 */
final class StringSchema implements Schema
{
    /**
     * @param array<string>|null $examples
     * @param array<string>|null $enum
     */
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly ?string $default = null,
        public readonly ?array $examples = null,
        public readonly ?bool $readOnly = null,
        public readonly ?bool $writeOnly = null,
        public readonly ?bool $deprecated = null,
        public readonly ?string $comment = null,
        public readonly ?array $enum = null,
        public readonly ?string $const = null,
        public readonly ?int $minLength = null,
        public readonly ?int $maxLength = null,
        public readonly ?StringFormat $format = null,
        public readonly ?string $contentMediaType = null,
        public readonly ?string $contentEncoding = null,
    ) {
    }

    /**
     * @param array<string>|null $examples
     * @param array<string>|null $enum
     */
    public function with(
        ?string $title = null,
        ?string $description = null,
        ?string $default = null,
        ?array $examples = null,
        ?bool $readOnly = null,
        ?bool $writeOnly = null,
        ?bool $deprecated = null,
        ?string $comment = null,
        ?array $enum = null,
        ?string $const = null,
        ?int $minLength = null,
        ?int $maxLength = null,
        ?StringFormat $format = null,
        ?string $contentMediaType = null,
        ?string $contentEncoding = null,
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
            $minLength ?? $this->minLength,
            $maxLength ?? $this->maxLength,
            $format ?? $this->format,
            $contentMediaType ?? $this->contentMediaType,
            $contentEncoding ?? $this->contentEncoding,
        );
    }

    public function jsonSerialize(): array
    {
        $array = [
            'type' => 'string',
            ...array_filter(get_object_vars($this)),
        ];
        if ($this->comment) {
            unset($array['comment']);
            $array['$comment'] = $this->comment;
        }
        if ($this->format !== null) {
            $array['format'] = $this->format->name;
        }
        return $array;
    }
}