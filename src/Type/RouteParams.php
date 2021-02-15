<?php


namespace Brace\Router\Type;


class RouteParams
{


    public function __construct (
        private array $params = []
    ){}

    public function has(string $key) : bool
    {
        return isset ($this->params[$key]);
    }

    public function get(string $key, $default=null) : ?string
    {
        if ( ! isset ($this->params[$key])) {
            return null;
        }
        return $this->params[$key] ?? $default;
    }
}