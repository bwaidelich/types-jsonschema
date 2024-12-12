<?php

declare(strict_types=1);

namespace PHPUnit\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Wwwision\TypesJSONSchema\Types\NotSchema;
use Wwwision\TypesJSONSchema\Types\Schema;

#[CoversClass(NotSchema::class)]
final class NotSchemaTest extends TestCase
{
    public function test(): void
    {
        $mockSchema1 = $this->getMockBuilder(Schema::class)->getMock();
        $mockSchema1->method('jsonSerialize')->willReturn(['type' => 'mock1']);
        $schema = new NotSchema($mockSchema1);
        self::assertJsonStringEqualsJsonString('{"not":{"type":"mock1"}}', json_encode($schema));
    }

}
