<?php

declare(strict_types=1);

namespace Wwwision\TypesJSONSchema\Types;

/**
 * @see https://json-schema.org/understanding-json-schema/reference/combining#not
 */
final class NotSchema implements Schema
{
    public function __construct(
        public readonly Schema $schema,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'not' => $this->schema,
        ];
    }
}
