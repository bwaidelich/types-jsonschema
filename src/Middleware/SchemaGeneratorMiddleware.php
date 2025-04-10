<?php

declare(strict_types=1);

namespace Wwwision\TypesJsonSchema\Middleware;

use Closure;
use Wwwision\JsonSchema\Schema as JsonSchema;
use Wwwision\Types\Schema as Types;

interface SchemaGeneratorMiddleware
{
    /**
     * @param Closure(Types\Schema): JsonSchema $next
     */
    public function __invoke(
        Types\Schema $schema,
        Closure $next,
    ): JsonSchema;
}
