<?php

declare(strict_types=1);

namespace spec\Brace\Router;

use Brace\Router\Attributes\BraceQuery;
use PhpSpec\ObjectBehavior;

final class QueryParameterResolverSpec extends ObjectBehavior
{
    public function it_infers_the_parameter_name_and_default_string_type(): void
    {
        $callback = static function (#[BraceQuery] $search): void {};
        $this->resolve(new \ReflectionFunction($callback))->shouldReturn([
            'search' => ['parameter' => 'search', 'type' => 'string', 'optional' => false,
                'default' => null, 'description' => ''],
        ]);
    }

    public function it_resolves_aliases_and_php_defaults(): void
    {
        $callback = static function (#[BraceQuery(name: 'limit', description: 'Page size')] int $count = 10): void {};
        $this->resolve(new \ReflectionFunction($callback))->shouldReturn([
            'limit' => ['parameter' => 'count', 'type' => 'int', 'optional' => true,
                'default' => 10, 'description' => 'Page size'],
        ]);
    }

    public function it_resolves_method_declarations_with_and_without_a_bound_parameter(): void
    {
        $fixture = new class {
            #[BraceQuery('search')]
            #[BraceQuery('limit', type: 'int', default: 20)]
            public function action(string $search): void {}
        };
        $this->resolve(new \ReflectionMethod($fixture, 'action'))->shouldReturn([
            'search' => ['parameter' => 'search', 'type' => 'string', 'optional' => false,
                'default' => null, 'description' => ''],
            'limit' => ['parameter' => null, 'type' => 'int', 'optional' => true,
                'default' => 20, 'description' => ''],
        ]);
    }

    public function it_does_not_treat_nullable_as_optional(): void
    {
        $callback = static function (#[BraceQuery] ?string $search): void {};
        $this->resolve(new \ReflectionFunction($callback))->shouldReturn([
            'search' => ['parameter' => 'search', 'type' => 'string', 'optional' => false,
                'default' => null, 'description' => ''],
        ]);
    }

    public function it_rejects_duplicate_keys(): void
    {
        $callback = static function (#[BraceQuery('same')] string $a, #[BraceQuery('same')] string $b): void {};
        $this->shouldThrow(\InvalidArgumentException::class)->duringResolve(new \ReflectionFunction($callback));
    }

    public function it_rejects_conflicting_types(): void
    {
        $callback = static function (#[BraceQuery(type: 'int')] string $search): void {};
        $this->shouldThrow(\InvalidArgumentException::class)->duringResolve(new \ReflectionFunction($callback));
    }

    public function it_only_allows_additional_keys_when_query_is_present(): void
    {
        $this->acceptsAdditional(new \ReflectionFunction(static function (array $query): void {}))->shouldReturn(true);
        $this->acceptsAdditional(new \ReflectionFunction(static function (): void {}))->shouldReturn(false);
    }
}
