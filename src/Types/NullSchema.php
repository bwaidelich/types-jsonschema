<?php

declare(strict_types=1);

namespace Wwwision\TypesJSONSchema\Types;

/**
 * @see https://json-schema.org/understanding-json-schema/reference/null
 */
final class NullSchema implements Schema
{
    public function jsonSerialize(): array
    {
        return ['type' => 'null'];
    }
}