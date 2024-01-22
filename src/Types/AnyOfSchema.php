<?php

declare(strict_types=1);

namespace Wwwision\TypesJSONSchema\Types;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * @see https://json-schema.org/understanding-json-schema/reference/combining#anyOf
 * @implements IteratorAggregate<Schema>
 */
final class AnyOfSchema implements IteratorAggregate, Schema
{
    /**
     * @param array<Schema> $items
     */
    private function __construct(private readonly array $items)
    {
    }

    public static function create(Schema ...$items): self
    {
        return new self($items);
    }

    public function jsonSerialize(): array
    {
        return [
            'anyOf' => $this->items,
        ];
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}
