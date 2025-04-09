<?php

declare(strict_types=1);

namespace Wwwision\TypesJSONSchema\Types;

use ArrayIterator;
use IteratorAggregate;
use Traversable;
use Wwwision\Types\Attributes\ListBased;

/**
 * @see https://json-schema.org/understanding-json-schema/reference/combining#oneof
 * @implements IteratorAggregate<Schema>
 */
#[ListBased(itemClassName: Schema::class)]
final class OneOfSchema implements IteratorAggregate, Schema
{
    /**
     * @param array<Schema> $items
     */
    private function __construct(
        private readonly array $items,
        public readonly Discriminator|null $discriminator,
    ) {}

    public static function create(Schema ...$items): self
    {
        return new self($items, null);
    }

    public function withDiscriminator(Discriminator $discriminator): self
    {
        return new self($this->items, $discriminator);
    }

    public function jsonSerialize(): array
    {
        return [
            'oneOf' => $this->items,
        ];
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}
