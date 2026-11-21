<?php

declare(strict_types=1);

namespace Brace\Router;

final class QueryParamsValidator
{
    public function validate(array $values, array $definitions, bool $allowAdditional = false): array
    {
        if (!$allowAdditional && array_diff_key($values, $definitions) !== []) {
            throw new \InvalidArgumentException('Unknown query parameter');
        }
        foreach ($definitions as $name => $definition) {
            if (!array_key_exists($name, $values)) {
                if (!$definition['optional']) {
                    throw new \InvalidArgumentException("Missing query parameter: $name");
                }
                $values[$name] = $definition['default'];
                continue;
            }
            $value = $values[$name];
            $valid = match ($definition['type']) {
                'string' => is_string($value),
                'array' => is_array($value),
                'int' => is_int($value) || (is_string($value) && preg_match('/^-?(0|[1-9][0-9]*)$/D', $value) && filter_var($value, FILTER_VALIDATE_INT) !== false),
                'float' => (is_string($value) || is_int($value) || is_float($value)) && is_numeric($value) && is_finite((float) $value),
                'bool' => is_bool($value) || in_array($value, ['true', 'false', '1', '0'], true),
                default => false,
            };
            if (!$valid) {
                throw new \InvalidArgumentException("Invalid query parameter: $name");
            }
            $values[$name] = match ($definition['type']) {
                'int' => (int) $value, 'float' => (float) $value,
                'bool' => in_array($value, [true, 'true', '1'], true),
                default => $value,
            };
        }
        return $values;
    }
}
