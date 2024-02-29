<?php

declare(strict_types=1);

namespace Wwwision\TypesJSONSchema\Types;

use ArrayIterator;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * @see https://json-schema.org/understanding-json-schema/reference/array#items
 * @implements IteratorAggregate<Schema>
 */
final class ArrayItems implements IteratorAggregate, JsonSerializable
{
    /**
     * @param array<Schema> $items
     */
    private function __construct(
        private readonly array $items
    ) {
    }

    public static function create(Schema ...$items): self
    {
        return new self($items);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @return array<Schema>
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
