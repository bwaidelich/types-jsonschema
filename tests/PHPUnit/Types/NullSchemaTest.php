<?php
declare(strict_types=1);

namespace PHPUnit\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Wwwision\TypesJSONSchema\Types\NullSchema;

#[CoversClass(NullSchema::class)]
final class NullSchemaTest extends TestCase
{

    public function test(): void
    {
        $schema = new NullSchema();
        self::assertJsonStringEqualsJsonString('{"type":"null"}', json_encode($schema));
    }

}