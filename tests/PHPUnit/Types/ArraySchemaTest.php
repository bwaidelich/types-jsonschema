<?php
declare(strict_types=1);

namespace PHPUnit\Types;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Wwwision\TypesJSONSchema\Types\ArrayItems;
use Wwwision\TypesJSONSchema\Types\ArraySchema;
use Wwwision\TypesJSONSchema\Types\Schema;

#[CoversClass(ArraySchema::class)]
#[CoversClass(ArrayItems::class)]
final class ArraySchemaTest extends TestCase
{

    public function test_empty(): void
    {
        $schema = new ArraySchema();
        self::assertJsonStringEqualsJsonString('{"type":"array"}', json_encode($schema));
    }

    public function test_minContains_without_contains_leads_to_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ArraySchema(minContains: 1);
    }

    public function test_maxContains_without_contains_leads_to_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ArraySchema(maxContains: 1);
    }

    public function test_fully_fledged(): void
    {
        $mockSchema1 = $this->getMockBuilder(Schema::class)->getMock();
        $mockSchema1->method('jsonSerialize')->willReturn(['type' => 'mock1']);
        $mockSchema2 = $this->getMockBuilder(Schema::class)->getMock();
        $mockSchema2->method('jsonSerialize')->willReturn(['type' => 'mock2']);
        $schema = new ArraySchema(
            title: 'some title',
            description: 'some description',
            default: ['some', 'default'],
            examples: ['foo' => ['foo', 'bar'], 'bar' => ['foos', 'bars']],
            readOnly: true,
            writeOnly: false,
            deprecated: true,
            comment: 'some comment',
            const: ['foo', 'bar'],
            items: $mockSchema1,
            prefixItems: ArrayItems::create($mockSchema1, $mockSchema2),
            unevaluatedItems: false,
            contains: $mockSchema1,
            minContains: 1,
            maxContains: 2,
            minItems: 3,
            maxItems: 4,
            uniqueItems: true,
        );
        self::assertJsonStringEqualsJsonString('{"type":"array","title":"some title","description":"some description","default":["some","default"],"examples":{"foo":["foo","bar"],"bar":["foos","bars"]},"readOnly":true,"writeOnly":false,"deprecated":true,"const":["foo","bar"],"items":{"type":"mock1"},"prefixItems":[{"type":"mock1"},{"type":"mock2"}],"unevaluatedItems":false,"contains":{"type":"mock1"},"minContains":1,"maxContains":2,"minItems":3,"maxItems":4,"uniqueItems":true,"$comment":"some comment"}', json_encode($schema));
    }

    public function test_wither(): void
    {
        $mockSchema1 = $this->getMockBuilder(Schema::class)->getMock();
        $mockSchema1->method('jsonSerialize')->willReturn(['type' => 'mock1']);
        $mockSchema2 = $this->getMockBuilder(Schema::class)->getMock();
        $mockSchema2->method('jsonSerialize')->willReturn(['type' => 'mock2']);
        $schema = new ArraySchema(
            title: 'some title',
            description: 'some description',
            default: ['some', 'default'],
            examples: ['foo' => ['foo', 'bar'], 'bar' => ['foos', 'bars']],
            readOnly: true,
            writeOnly: false,
            deprecated: true,
            comment: 'some comment',
            const: ['foo', 'bar'],
            items: $mockSchema1,
            prefixItems: ArrayItems::create($mockSchema1, $mockSchema2),
            unevaluatedItems: false,
            contains: $mockSchema1,
            minContains: 1,
            maxContains: 2,
            minItems: 3,
            maxItems: 4,
            uniqueItems: true,
        );
        $schema = $schema->with(
            title: 'some changed title',
            description: 'some changed description',
            default: ['some', 'changed', 'default'],
            examples: ['foo' => ['changed', 'bar'], 'bar' => ['foos', 'bars']],
            readOnly: false,
            writeOnly: true,
            deprecated: false,
            comment: 'some changed comment',
            const: ['foo', 'changed'],
            items: $mockSchema2,
            prefixItems: ArrayItems::create($mockSchema2, $mockSchema1),
            unevaluatedItems: ArrayItems::create($mockSchema1),
            contains: $mockSchema2,
            minContains: 2,
            maxContains: 3,
            minItems: 4,
            maxItems: 5,
            uniqueItems: false,
        );
        self::assertJsonStringEqualsJsonString('{"type":"array","title":"some changed title","description":"some changed description","default":["some","changed","default"],"examples":{"foo":["changed","bar"],"bar":["foos","bars"]},"readOnly":false,"writeOnly":true,"deprecated":false,"const":["foo","changed"],"items":{"type":"mock2"},"prefixItems":[{"type":"mock2"},{"type":"mock1"}],"unevaluatedItems":[{"type":"mock1"}],"contains":{"type":"mock2"},"minContains":2,"maxContains":3,"minItems":4,"maxItems":5,"uniqueItems":false,"$comment":"some changed comment"}', json_encode($schema));
    }

    public function test_withDescription(): void
    {
        $schema = new ArraySchema();
        $schema = $schema->withDescription('Some changed description');
        self::assertJsonStringEqualsJsonString('{"type":"array","description":"Some changed description"}', json_encode($schema));
    }

}