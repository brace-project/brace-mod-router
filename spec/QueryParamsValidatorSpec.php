<?php

declare(strict_types=1);

namespace spec\Brace\Router;

use PhpSpec\ObjectBehavior;

final class QueryParamsValidatorSpec extends ObjectBehavior
{
    private function definitions(string $type = 'string', bool $optional = false, mixed $default = null): array
    {
        return ['value' => ['type' => $type, 'optional' => $optional, 'default' => $default]];
    }

    public function it_validates_and_converts_supported_types(): void
    {
        foreach ([['string', 'text', 'text'], ['int', '42', 42], ['int', '0', 0],
            ['float', '2.5', 2.5], ['bool', 'false', false], ['bool', 'true', true],
            ['bool', '0', false], ['bool', '1', true], ['array', ['x'], ['x']]] as [$type, $input, $expected]) {
            $this->validate(['value' => $input], $this->definitions($type))->shouldReturn(['value' => $expected]);
        }
    }

    public function it_rejects_missing_required_values(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->duringValidate([], $this->definitions());
    }

    public function it_applies_defaults_including_null(): void
    {
        $this->validate([], $this->definitions('int', true, 10))->shouldReturn(['value' => 10]);
        $this->validate([], $this->definitions('string', true))->shouldReturn(['value' => null]);
    }

    public function it_rejects_invalid_shapes_and_values(): void
    {
        foreach ([['string', []], ['int', '1.5'], ['int', '999999999999999999999999999999'],
            ['float', '1e9999'], ['bool', 'yes'], ['array', 'text'], ['string', null]] as [$type, $input]) {
            $this->shouldThrow(\InvalidArgumentException::class)->duringValidate(['value' => $input], $this->definitions($type));
        }
    }

    public function it_rejects_undeclared_keys_by_default(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->duringValidate(['extra' => 'x'], []);
    }

    public function it_preserves_additional_values_when_allowed_and_still_converts_declared_values(): void
    {
        $this->validate(['value' => '2', 'extra' => ['x']], $this->definitions('int'), true)
            ->shouldReturn(['value' => 2, 'extra' => ['x']]);
        $this->shouldThrow(\InvalidArgumentException::class)
            ->duringValidate(['value' => 'invalid', 'extra' => 'x'], $this->definitions('int'), true);
    }
}
