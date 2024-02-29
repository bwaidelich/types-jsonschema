<?php
declare(strict_types=1);

namespace PHPUnit\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Wwwision\TypesJSONSchema\Types\ObjectProperties;

#[CoversClass(ObjectProperties::class)]
final class ObjectPropertiesTest extends TestCase
{

    public function test_empty(): void
    {
        $objectProperties = ObjectProperties::create();
        self::assertSame([], iterator_to_array($objectProperties));
    }

}