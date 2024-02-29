<?php
declare(strict_types=1);

namespace PHPUnit\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Wwwision\TypesJSONSchema\Types\BooleanSchema;

#[CoversClass(BooleanSchema::class)]
final class BooelanSchemaTest extends TestCase
{

    public function test_fully_fledged(): void
    {
        $schema = new BooleanSchema(
            title: 'some title',
            description: 'some description',
            default: true,
            examples: [true, false],
            readOnly: true,
            writeOnly: false,
            deprecated: true,
            comment: 'some comment',
            const: true,
        );
        self::assertJsonStringEqualsJsonString('{"type":"boolean","title":"some title","description":"some description","default":true,"examples":[true,false],"readOnly":true,"writeOnly":false,"deprecated":true,"const":true,"$comment":"some comment"}', json_encode($schema));
    }

    public function test_wither(): void
    {
        $schema = new BooleanSchema(
            title: 'some title',
            description: 'some description',
            default: true,
            examples: [true, false],
            readOnly: true,
            writeOnly: false,
            deprecated: true,
            comment: 'some comment',
            const: true,
        );
        $schema = $schema->with(
            title: 'some changed title',
            description: 'some changed description',
            default: false,
            examples: [false, true],
            readOnly: false,
            writeOnly: true,
            deprecated: false,
            comment: 'some changed comment',
            const: false,
        );
        self::assertJsonStringEqualsJsonString('{"type":"boolean","title":"some changed title","description":"some changed description","default":false,"examples":[false,true],"readOnly":false,"writeOnly":true,"deprecated":false,"const":false,"$comment":"some changed comment"}', json_encode($schema));
    }

    public function test_withDescription(): void
    {
        $schema = new BooleanSchema();
        $schema = $schema->withDescription('Some changed description');
        self::assertJsonStringEqualsJsonString('{"type":"boolean","description":"Some changed description"}', json_encode($schema));
    }

}