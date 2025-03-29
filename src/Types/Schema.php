<?php

declare(strict_types=1);

namespace Wwwision\TypesJSONSchema\Types;

use JsonSerializable;
use Wwwision\Types\Attributes\Discriminator as TypeDiscriminator;

#[TypeDiscriminator(propertyName: 'type', mapping: [
    'allOf' => AllOfSchema::class,
    'anyOf' => AnyOfSchema::class,
    'array' => ArraySchema::class,
    'boolean' => BooleanSchema::class,
    'integer' => IntegerSchema::class,
    'not' => NotSchema::class,
    'null' => NullSchema::class,
    'number' => NumberSchema::class,
    'object' => ObjectSchema::class,
    'oneOf' => OneOfSchema::class,
    '$ref' => ReferenceSchema::class,
    'string' => StringSchema::class,
])]
interface Schema extends JsonSerializable
{
    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array;
}
