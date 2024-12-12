<?php

declare(strict_types=1);

namespace PHPUnit\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Wwwision\TypesJSONSchema\Types\ArrayItems;

#[CoversClass(ArrayItems::class)]
final class ArrayItemsTest extends TestCase
{
    public function test_empty(): void
    {
        $arrayItems = ArrayItems::create();
        self::assertSame([], iterator_to_array($arrayItems));
    }

}
