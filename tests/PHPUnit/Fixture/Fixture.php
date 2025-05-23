<?php

declare(strict_types=1);

namespace Wwwision\TypesJsonSchema\Tests\PHPUnit\Fixture;

use ArrayIterator;
use DateTimeImmutable;
use IteratorAggregate;
use JsonSerializable;
use stdClass;
use Traversable;
use Wwwision\Types\Attributes\Description;
use Wwwision\Types\Attributes\Discriminator;
use Wwwision\Types\Attributes\FloatBased;
use Wwwision\Types\Attributes\IntegerBased;
use Wwwision\Types\Attributes\ListBased;
use Wwwision\Types\Attributes\StringBased;
use Wwwision\Types\Exception\CoerceException;
use Wwwision\Types\Parser;
use Wwwision\Types\Schema\StringTypeFormat;

use function Wwwision\Types\instantiate;

#[StringBased(minLength: 3, maxLength: 20)]
#[Description('First name of a person')]
final class GivenName implements SomeInterface, SomeInterfaceWithDiscriminator, JsonSerializable
{
    private function __construct(public readonly string $value) {}

    public function someMethod(): string
    {
        return 'bar';
    }

    public function someOtherMethod(): FamilyName
    {
        return instantiate(self::class, $this->value);
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}

#[StringBased(minLength: 3, maxLength: 20)]
#[Description('Last name of a person')]
final class FamilyName implements JsonSerializable, SomeInterface, SomeInterfaceWithDiscriminator
{
    private function __construct(public readonly string $value) {}

    public function someMethod(): string
    {
        return 'bar';
    }

    public function someOtherMethod(): FamilyName
    {
        return instantiate(self::class, $this->value);
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}

#[IntegerBased(minimum: 1, maximum: 120)]
#[Description('The age of a person in years')]
final class Age
{
    private function __construct(public readonly int $value) {}
}


#[Description('First and last name of a person')]
final class FullName implements SomeInterface
{
    public function __construct(
        #[Description('Overridden given name description')]
        public readonly GivenName $givenName,
        public readonly FamilyName $familyName,
    ) {}

    public function someMethod(): string
    {
        return 'baz';
    }

    public function someOtherMethod(): FamilyName
    {
        return $this->familyName;
    }
}

/** @implements IteratorAggregate<FullName> */
#[ListBased(itemClassName: FullName::class, minCount: 2, maxCount: 5)]
final class FullNames implements IteratorAggregate
{
    private array $fullNames;

    /** @param array<FullName> $fullNames */
    private function __construct(FullName... $fullNames)
    {
        $this->fullNames = $fullNames;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->fullNames);
    }
}

#[ListBased(itemClassName: GivenName::class, maxCount: 4)]
final class GivenNames implements IteratorAggregate, JsonSerializable
{
    /** @param array<GivenName> $givenNames */
    private function __construct(private readonly array $givenNames) {}

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->givenNames);
    }

    public function jsonSerialize(): array
    {
        return $this->givenNames;
    }
}

#[ListBased(itemClassName: Uri::class)]
final class UriMap implements IteratorAggregate, JsonSerializable
{
    private function __construct(private readonly array $entries)
    {
        if (array_keys($entries) !== array_filter(\array_keys($entries), '\is_string')) {
            throw CoerceException::custom('Expected associative array with string keys', $entries, Parser::getSchema(self::class), );
        }
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->entries);
    }

    public function jsonSerialize(): array
    {
        return $this->entries;
    }

}

#[StringBased(pattern: '^(?!magic).*')]
final class NotMagic
{
    private function __construct(public readonly string $value) {}
}

#[StringBased(format: StringTypeFormat::email)]
final class EmailAddress
{
    private function __construct(public readonly string $value) {}
}

#[StringBased(format: StringTypeFormat::uri)]
final class Uri implements JsonSerializable
{
    private function __construct(public readonly string $value) {}

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}

#[StringBased(format: StringTypeFormat::date)]
final class Date
{
    private function __construct(public readonly string $value)
    {
        $now = new DateTimeImmutable();
        if (DateTimeImmutable::createFromFormat('Y-m-d', $this->value) > $now) {
            throw CoerceException::custom('Future dates are not allowed', $value, Parser::getSchema(self::class), ['some' => 'param']);
        }
    }
}

#[StringBased(format: StringTypeFormat::date_time)]
final class DateTime
{
    private function __construct(public readonly string $value) {}
}

#[StringBased(format: StringTypeFormat::uuid)]
final class Uuid
{
    private function __construct(public readonly string $value) {}
}

#[Description('honorific title of a person')]
enum Title
{
    #[Description('for men, regardless of marital status, who do not have another professional or academic title')]
    case MR;
    #[Description('for married women who do not have another professional or academic title')]
    case MRS;
    #[Description('for girls, unmarried women and married women who continue to use their maiden name')]
    case MISS;
    #[Description('for women, regardless of marital status or when marital status is unknown')]
    case MS;
    #[Description('for any other title that does not match the above')]
    case OTHER;
}

#[Description('A number')]
enum Number: int
{
    #[Description('The number 2')]
    case TWO = 2;
    case FOUR = 4;
    case FIVE = 5;
}

enum RomanNumber: string
{
    case I = '1';
    #[Description('random description')]
    case II = '2';
    case III = '3';
    case IV = '4';
}

final class NestedShape
{
    public function __construct(
        public readonly ShapeWithOptionalTypes $shapeWithOptionalTypes,
        public readonly ShapeWithBool $shapeWithBool,
    ) {}
}

final class ShapeWithOptionalTypes
{
    public function __construct(
        public readonly FamilyName $stringBased,
        public readonly null|FamilyName $optionalStringBased = null,
        #[Description('Some description')]
        public readonly null|int $optionalInt = null,
        public readonly null|bool $optionalBool = false,
        public readonly null|string $optionalString = null,
    ) {}
}

final class ShapeWithInvalidObjectProperty
{
    public function __construct(
        public readonly stdClass $someProperty,
    ) {}
}

final class ShapeWithBool
{
    private function __construct(
        #[Description('Description for literal bool')]
        public readonly bool $value,
    ) {}
}

final class ShapeWithInt
{
    private function __construct(
        #[Description('Description for literal int')]
        public readonly int $value,
    ) {}
}

final class ShapeWithString
{
    private function __construct(
        #[Description('Description for literal string')]
        public readonly string $value,
    ) {}
}

#[Description('SomeInterface description')]
interface SomeInterface
{
    #[Description('Custom description for "someMethod"')]
    public function someMethod(): string;
    #[Description('Custom description for "someOtherMethod"')]
    public function someOtherMethod(): null|FamilyName;
}

#[Discriminator(propertyName: 'type', mapping: ['given' => GivenName::class, 'family' => FamilyName::class])]
interface SomeInterfaceWithDiscriminator {}

class SomeShapeWithDiscriminatedUnionType
{
    public function __construct(
        #[Discriminator(propertyName: 't', mapping: ['g' => GivenName::class, 'f' => FamilyName::class])]
        public readonly GivenName|FamilyName $name,
    ) {}
}

class SomeShapeWithInterfaceProperty
{
    public function __construct(
        public readonly SomeInterfaceWithDiscriminator $property,
    ) {}
}

#[FloatBased(minimum: -180.0, maximum: 180.5)]
final class Longitude
{
    private function __construct(
        public readonly float $value,
    ) {}
}

#[FloatBased(minimum: -90, maximum: 90)]
final class Latitude
{
    private function __construct(
        public readonly float $value,
    ) {}
}

final class GeoCoordinates
{
    public function __construct(
        public readonly Longitude $longitude,
        public readonly Latitude $latitude,
    ) {}
}


interface SomeInvalidInterface
{
    public function methodWithParameters(string|null $param = null): string;
}

#[StringBased(minLength: 10, maxLength: 2, pattern: '^foo$', format: StringTypeFormat::email)]
final class ImpossibleString
{
    private function __construct(public readonly string $value) {}
}

#[IntegerBased(minimum: 10, maximum: 2)]
final class ImpossibleInt
{
    private function __construct(public readonly string $value) {}
}

#[FloatBased(minimum: 10.23, maximum: 2.45)]
final class ImpossibleFloat
{
    private function __construct(public readonly string $value) {}
}

#[ListBased(itemClassName: GivenName::class, minCount: 10, maxCount: 2)]
final class ImpossibleList
{
    private function __construct(private readonly array $items) {}
}
