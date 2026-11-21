<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/autoload.php';

use Brace\Router\Attributes\BraceQuery;
use Brace\Router\QueryParameterResolver;

$resolver = new QueryParameterResolver();
$callback = static function (#[BraceQuery(name: 'limit')] int $count = 10): void {};
$definitions = $resolver->resolve($resolver->reflect($callback));
if ($definitions['limit']['parameter'] !== 'count' || !$definitions['limit']['optional'] || $definitions['limit']['default'] !== 10) {
    throw new RuntimeException('Parameter alias/default resolution failed');
}
$fixture = new class {
    #[BraceQuery('search', description: 'Search text')]
    #[BraceQuery('limit', type: 'int', default: 20)]
    public function action(string $search, array $query): void {}
};
$reflection = $resolver->reflect([$fixture, 'action']);
$definitions = $resolver->resolve($reflection);
if ($definitions['search']['type'] !== 'string' || $definitions['search']['optional'] || $definitions['limit']['parameter'] !== null || !$resolver->acceptsAdditional($reflection)) {
    throw new RuntimeException('Method declaration/open query resolution failed');
}
echo "QueryParameterResolver: OK\n";
