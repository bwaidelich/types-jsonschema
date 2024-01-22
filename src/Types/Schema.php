<?php

declare(strict_types=1);

namespace Wwwision\TypesJSONSchema\Types;

use JsonSerializable;

interface Schema extends JsonSerializable
{
    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array;
}