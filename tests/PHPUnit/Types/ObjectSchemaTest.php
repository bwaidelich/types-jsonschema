<?php
declare(strict_types=1);

namespace PHPUnit\Types;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Wwwision\TypesJSONSchema\Types\ObjectProperties;
use Wwwision\TypesJSONSchema\Types\ObjectSchema;
use Wwwision\TypesJSONSchema\Types\Schema;
use Wwwision\TypesJSONSchema\Types\StringSchema;

#[CoversClass(ObjectSchema::class)]
#[CoversClass(ObjectProperties::class)]
#[CoversClass(StringSchema::class)]
final class ObjectSchemaTest extends TestCase
{

    public function test_fully_fledged(): void
    {
        $mockSchema1 = $this->getMockBuilder(Schema::class)->getMock();
        $mockSchema1->method('jsonSerialize')->willReturn(['type' => 'mock1']);
        $mockSchema2 = $this->getMockBuilder(Schema::class)->getMock();
        $mockSchema2->method('jsonSerialize')->willReturn(['type' => 'mock2']);
        $schema = new ObjectSchema(
            title: 'some title',
            description: 'some description',
            default: ['foo' => 'bar'],
            examples: [['bar' => 'baz'], ['foos' => 'bars']],
            readOnly: true,
            writeOnly: false,
            deprecated: true,
            comment: 'some comment',
            const: ['constant' => 'value'],
            properties: ObjectProperties::create(...['prop1' => $mockSchema1, 'prop2' => $mockSchema2]),
            additionalProperties: true,
            required: ['prop1'],
            propertyNames: new StringSchema(pattern: '^[A-Za-z_][A-Za-z0-9_]*$'),
            minProperties: 1,
            maxProperties: 2,
        );
        self::assertJsonStringEqualsJsonString('{"type":"object","title":"some title","description":"some description","default":{"foo":"bar"},"examples":[{"bar":"baz"},{"foos":"bars"}],"readOnly":true,"writeOnly":false,"deprecated":true,"const":{"constant":"value"},"properties":{"prop1":{"type":"mock1"},"prop2":{"type":"mock2"}},"additionalProperties":true,"required":["prop1"],"propertyNames":{"type":"string","pattern":"^[A-Za-z_][A-Za-z0-9_]*$"},"minProperties":1,"maxProperties":2,"$comment":"some comment"}', json_encode($schema));
    }

    public function test_wither(): void
    {
        $mockSchema1 = $this->getMockBuilder(Schema::class)->getMock();
        $mockSchema1->method('jsonSerialize')->willReturn(['type' => 'mock1']);
        $mockSchema2 = $this->getMockBuilder(Schema::class)->getMock();
        $mockSchema2->method('jsonSerialize')->willReturn(['type' => 'mock2']);
        $schema = new ObjectSchema(
            title: 'some title',
            description: 'some description',
            default: ['foo' => 'bar'],
            examples: [['bar' => 'baz'], ['foos' => 'bars']],
            readOnly: true,
            writeOnly: false,
            deprecated: true,
            comment: 'some comment',
            const: ['constant' => 'value'],
            properties: ObjectProperties::create(...['prop1' => $mockSchema1, 'prop2' => $mockSchema2]),
            additionalProperties: true,
            required: ['prop1'],
            propertyNames: new StringSchema(pattern: '^[A-Za-z_][A-Za-z0-9_]*$'),
            minProperties: 1,
            maxProperties: 2,
        );
        $schema = $schema->with(
            title: 'some changed title',
            description: 'some changed description',
            default: ['foo' => 'bar2'],
            examples: [['bar2' => 'baz'], ['foos' => 'bars changed']],
            readOnly: false,
            writeOnly: true,
            deprecated: false,
            comment: 'some changed comment',
            const: ['constant' => 'value changed'],
            properties: ObjectProperties::create(...['prop1' => $mockSchema2, 'propX' => $mockSchema1]),
            additionalProperties: false,
            required: ['propX'],
            propertyNames: new StringSchema(pattern: '^.*$'),
            minProperties: 2,
            maxProperties: 3,
        );
        self::assertJsonStringEqualsJsonString('{"type":"object","title":"some changed title","description":"some changed description","default":{"foo":"bar2"},"examples":[{"bar2":"baz"},{"foos":"bars changed"}],"readOnly":false,"writeOnly":true,"deprecated":false,"const":{"constant":"value changed"},"properties":{"prop1":{"type":"mock2"},"propX":{"type":"mock1"}},"additionalProperties":false,"required":["propX"],"propertyNames":{"type":"string","pattern":"^.*$"},"minProperties":2,"maxProperties":3,"$comment":"some changed comment"}', json_encode($schema));
    }

    public function test_withDescription(): void
    {
        $schema = new ObjectSchema();
        $schema = $schema->withDescription('Some changed description');
        self::assertJsonStringEqualsJsonString('{"type":"object","description":"Some changed description"}', json_encode($schema));
    }

}