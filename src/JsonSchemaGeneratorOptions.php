<?php

declare(strict_types=1);

namespace Wwwision\TypesJsonSchema;

use Wwwision\TypesJsonSchema\Middleware\SchemaGeneratorMiddleware;

final class JsonSchemaGeneratorOptions
{
    /**
     * @param array<SchemaGeneratorMiddleware> $middlewares
     */
    private function __construct(
        public readonly bool $includeDiscriminator,
        public readonly array $middlewares = [],
    ) {}

    public static function create(
        bool $includeDiscriminator = false,
    ): self {
        return new self(
            includeDiscriminator: $includeDiscriminator,
        );
    }

    public function withMiddleware(SchemaGeneratorMiddleware $middleware): self
    {
        return new self(
            includeDiscriminator: $this->includeDiscriminator,
            middlewares: [...$this->middlewares, $middleware],
        );
    }
}
