<?php


namespace Brace\Router\Type;


class UndefinedRoute extends Route
{

    public function isDefined() : bool
    {
        return false;
    }

    public function __construct(string $requestMethod, string $requestPath)
    {
        parent::__construct($routeOrig=null, $requestPath, $requestMethod, new RouteParams([]), null);
    }
}