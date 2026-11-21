<?php

declare(strict_types=1);

namespace Brace\Router\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PARAMETER | \Attribute::IS_REPEATABLE)]
final class BraceQuery
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly string $description = '',
        public readonly ?string $type = null,
        public readonly mixed $default = null,
    ) {}
}
