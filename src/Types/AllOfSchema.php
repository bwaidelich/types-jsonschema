<?php

declare(strict_types=1);

namespace Wwwision\TypesJSONSchema\Types;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * @see https://json-schema.org/understanding-json-schema/reference/combining#allof
 * @implements IteratorAggregate<Schema>
 */
final class AllOfSchema implements IteratorAggregate, Schema
{
    /**
     * @param array<Schema> $items
     */
    private function __construct(private readonly array $items) {}

    public static function create(Schema ...$items): self
    {
        return new self($items);
    }

    public function jsonSerialize(): array
    {
        return [
            'allOf' => $this->items,
        ];
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}
