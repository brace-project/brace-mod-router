<?php

declare(strict_types=1);

namespace Brace\Router;

use Brace\Router\Attributes\BraceQuery;

final class QueryParameterResolver
{
    public function reflect(callable|array $callback): \ReflectionFunctionAbstract
    {
        return is_array($callback)
            ? new \ReflectionMethod($callback[0], $callback[1])
            : new \ReflectionFunction(\Closure::fromCallable($callback));
    }

    /** @return array<string,array{parameter:?string,type:string,optional:bool,default:mixed,description:string}> */
    public function resolve(\ReflectionFunctionAbstract $function): array
    {
        $params = [];
        foreach ($function->getParameters() as $parameter) {
            $params[$parameter->getName()] = $parameter;
        }
        $result = [];
        $add = function (\ReflectionAttribute $attribute, ?\ReflectionParameter $parameter) use (&$result): void {
            $query = $attribute->newInstance();
            $name = $query->name ?? $parameter?->getName();
            if ($name === null || $name === '' || isset($result[$name])) {
                throw new \InvalidArgumentException('Missing or duplicate BraceQuery name');
            }
            $native = $parameter?->getType();
            if ($native !== null && (!$native instanceof \ReflectionNamedType || !$native->isBuiltin())) {
                throw new \InvalidArgumentException("Unsupported query parameter type: $name");
            }
            $type = $query->type ?? $native?->getName() ?? 'string';
            if (!in_array($type, ['string', 'int', 'float', 'bool', 'array'], true)
                || ($native !== null && $native->getName() !== $type)) {
                throw new \InvalidArgumentException("Unsupported or conflicting query type: $name");
            }
            foreach ($result as $definition) {
                if ($parameter !== null && $definition['parameter'] === $parameter->getName()) {
                    throw new \InvalidArgumentException('Duplicate query target parameter');
                }
            }
            $arguments = $attribute->getArguments();
            $optional = $parameter !== null ? $parameter->isDefaultValueAvailable()
                : (array_key_exists('default', $arguments) || array_key_exists(3, $arguments));
            $result[$name] = [
                'parameter' => $parameter?->getName(), 'type' => $type,
                'optional' => $optional,
                'default' => $parameter !== null && $optional ? $parameter->getDefaultValue() : $query->default,
                'description' => $query->description,
            ];
        };
        foreach ($function->getAttributes(BraceQuery::class) as $attribute) {
            $name = $attribute->newInstance()->name;
            $add($attribute, $params[$name ?? ''] ?? null);
        }
        foreach ($params as $parameter) {
            foreach ($parameter->getAttributes(BraceQuery::class) as $attribute) {
                $add($attribute, $parameter);
            }
        }
        return $result;
    }

    public function acceptsAdditional(\ReflectionFunctionAbstract $function): bool
    {
        foreach ($function->getParameters() as $parameter) {
            if ($parameter->getName() === 'query') {
                return true;
            }
        }
        return false;
    }
}
