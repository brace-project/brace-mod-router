<?php

declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/autoload.php';

use Brace\Router\QueryParamsValidator;

$validator = new QueryParamsValidator();
foreach (['string' => 'hello', 'int' => '42', 'float' => '2.5', 'bool' => 'false', 'array' => ['a']] as $type => $input) {
    $definitions = ['value' => ['type' => $type, 'optional' => false, 'default' => null]];
    $value = $validator->validate(['value' => $input], $definitions)['value'];
    if (get_debug_type($value) !== $type) {
        throw new RuntimeException("Wrong converted type: $type");
    }
}
$definitions = ['value' => ['type' => 'int', 'optional' => false, 'default' => null]];
foreach ([[], ['value' => []], ['value' => '1.2'], ['value' => '9999999999999999999999999999'], ['value' => '1', 'extra' => 'x']] as $invalid) {
    try {
        $validator->validate($invalid, $definitions);
    } catch (InvalidArgumentException) {
        continue;
    }
    throw new RuntimeException('Invalid query accepted');
}
if ($validator->validate(['value' => '1', 'extra' => ['x']], $definitions, true)['extra'] !== ['x']) {
    throw new RuntimeException('Additional query value changed');
}
if ($validator->validate([], ['v' => ['type' => 'int', 'optional' => true, 'default' => 10]]) !== ['v' => 10]) {
    throw new RuntimeException('Default not applied');
}
echo "QueryParamsValidator: OK\n";
