<?php


namespace Brace\Router\Type;


class RouteParams
{

    private $params = [];

    public function __construct (array $params)
    {

    }

    public function has(string $key) : bool
    {
        return isset ($this->params[$key]);
    }

    public function get(string $key, $default=null) : string
    {
        if ( ! isset ($this->params[$key])) {
            if ($default instanceof \Exception)
                throw $default;
            if ($default === null)
                throw new \InvalidArgumentException("RouteParam '$key' not existing.");
        }
        return $this->params[$key] ?? $default;
    }
}