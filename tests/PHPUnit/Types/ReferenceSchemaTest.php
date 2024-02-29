<?php
declare(strict_types=1);

namespace PHPUnit\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Wwwision\TypesJSONSchema\Types\ReferenceSchema;

#[CoversClass(ReferenceSchema::class)]
final class ReferenceSchemaTest extends TestCase
{

    public function test(): void
    {
        $schema = new ReferenceSchema('some-ref');
        self::assertJsonStringEqualsJsonString('{"$ref": "some-ref"}', json_encode($schema));
    }
}