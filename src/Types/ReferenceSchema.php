<?php

declare(strict_types=1);

namespace Wwwision\TypesJSONSchema\Types;

/**
 * @see https://json-schema.org/understanding-json-schema/structuring#dollarref
 */
final class ReferenceSchema implements Schema
{
    public function __construct(
        public readonly string $ref
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return ['$ref' => $this->ref];
    }
}