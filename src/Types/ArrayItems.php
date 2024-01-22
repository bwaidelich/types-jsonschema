<?php

declare(strict_types=1);

namespace Wwwision\TypesJSONSchema\Types;

use ArrayIterator;
use IteratorAggregate;
use Traversable;
use Webmozart\Assert\Assert;

/**
 * @see https://json-schema.org/understanding-json-schema/reference/array#items
 * @implements IteratorAggregate<Schema>
 */
final class ArrayItems implements IteratorAggregate
{
    /**
     * @param array<string, Schema> $items
     */
    private function __construct(
        private readonly array $items
    ) {
    }

    public static function create(Schema ...$items): self
    {
        Assert::isMap($items, 'Array items has to be a map with string keys');
        return new self($items);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}