<?php
declare(strict_types=1);

namespace Wwwision\TypesJSONSchema\Tests\PHPUnit\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Wwwision\TypesJSONSchema\Types\AllOfSchema;
use Wwwision\TypesJSONSchema\Types\Schema;

#[CoversClass(AllOfSchema::class)]
final class AllOfSchemaTest extends TestCase
{

    public function test_empty(): void
    {
        $schema = AllOfSchema::create();
        self::assertJsonStringEqualsJsonString('{"allOf": []}', json_encode($schema));
    }

    public function test_with_two_items(): void
    {
        $mockSchema1 = $this->getMockBuilder(Schema::class)->getMock();
        $mockSchema1->method('jsonSerialize')->willReturn(['type' => 'mock1']);
        $mockSchema2 = $this->getMockBuilder(Schema::class)->getMock();
        $mockSchema2->method('jsonSerialize')->willReturn(['type' => 'mock2']);
        $schema = AllOfSchema::create($mockSchema1, $mockSchema2);
        self::assertJsonStringEqualsJsonString('{"allOf":[{"type":"mock1"},{"type":"mock2"}]}', json_encode($schema));
    }

    public function test_is_iterable(): void
    {
        $mockSchema1 = $this->getMockBuilder(Schema::class)->getMock();
        $mockSchema1->method('jsonSerialize')->willReturn(['type' => 'mock1']);
        $mockSchema2 = $this->getMockBuilder(Schema::class)->getMock();
        $mockSchema2->method('jsonSerialize')->willReturn(['type' => 'mock2']);
        $schema = AllOfSchema::create($mockSchema1, $mockSchema2);
        self::assertSame([$mockSchema1, $mockSchema2], iterator_to_array($schema));
    }

}