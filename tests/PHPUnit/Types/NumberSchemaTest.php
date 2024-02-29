<?php
declare(strict_types=1);

namespace PHPUnit\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Wwwision\TypesJSONSchema\Types\NumberSchema;

#[CoversClass(NumberSchema::class)]
final class NumberSchemaTest extends TestCase
{

    public function test_fully_fledged(): void
    {
        $schema = new NumberSchema(
            title: 'some title',
            description: 'some description',
            default: 123.45,
            examples: [321, 222.5],
            readOnly: true,
            writeOnly: false,
            deprecated: true,
            comment: 'some comment',
            enum: [123.3,321,222],
            const: 321.4,
            multipleOf: 1.3,
            minimum: 3.5,
            exclusiveMinimum: true,
            maximum:  10.66,
            exclusiveMaximum: true,
        );
        self::assertJsonStringEqualsJsonString('{"type":"number","title":"some title","description":"some description","default":123.45,"examples":[321,222.5],"readOnly":true,"writeOnly":false,"deprecated":true,"enum":[123.3,321,222],"const":321.4,"multipleOf":1.3,"minimum":3.5,"exclusiveMinimum":true,"maximum":10.66,"exclusiveMaximum":true,"$comment":"some comment"}', json_encode($schema));
    }

    public function test_wither(): void
    {
        $schema = new NumberSchema(
            title: 'some title',
            description: 'some description',
            default: 123,
            examples: [321.5, 222],
            readOnly: true,
            writeOnly: false,
            deprecated: true,
            comment: 'some comment',
            enum: [123,321.34,222],
            const: 321.2,
            multipleOf: 2,
            minimum: 3,
            exclusiveMinimum: true,
            maximum:  10.3,
            exclusiveMaximum: true,
        );
        $schema = $schema->with(
            title: 'some changed title',
            description: 'some changed description',
            default: 321.32,
            examples: [333.12, 124],
            readOnly: false,
            writeOnly: true,
            deprecated: false,
            comment: 'some changed comment',
            enum: [333,124.55,222],
            const: 123.23,
            multipleOf: 3.2,
            minimum: 4.4,
            exclusiveMinimum: false,
            maximum:  12.2,
            exclusiveMaximum: false,
        );
        self::assertJsonStringEqualsJsonString('{"type":"number","title":"some changed title","description":"some changed description","default":321.32,"examples":[333.12,124],"readOnly":false,"writeOnly":true,"deprecated":false,"enum":[333,124.55,222],"const":123.23,"multipleOf":3.2,"minimum":4.4,"exclusiveMinimum":false,"maximum":12.2,"exclusiveMaximum":false,"$comment":"some changed comment"}', json_encode($schema));
    }

    public function test_withDescription(): void
    {
        $schema = new NumberSchema();
        $schema = $schema->withDescription('Some changed description');
        self::assertJsonStringEqualsJsonString('{"type":"number","description":"Some changed description"}', json_encode($schema));
    }

}