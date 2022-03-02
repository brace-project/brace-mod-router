<?php


namespace Brace\Router;


use Brace\Router\Type\Route;
use Brace\Router\Type\RouteParams;
use Brace\Router\Type\UndefinedRoute;
use Psr\Http\Message\ServerRequestInterface;

class Router
{

    private $routes = [];

    public function delegate(string $route, string $className) : self
    {
        $this->routes[] = ["route"=>$route, "delegate"=>$className];
        return $this;
    }


    /**
     * @param string $route
     * @param callable|class-string $fn
     * @return $this
     */
    public function on(string $route, callable|string $fn) : self
    {
        if (is_string($fn) && ! class_exists($fn))
            throw new \InvalidArgumentException("Parameter 2 must be Closure or class-string: '$fn' class not existing");

        $this->routes[] = ["route"=>$route, "call" => $fn];
        return $this;
    }

    /**
     * @param ServerRequestInterface $serverRequest
     * @return Route
     */
    public function _evalRoute(ServerRequestInterface $serverRequest) : Route
    {
        foreach ($this->routes as $curRoute) {
            $routeParams = [];
            if ( ! RouteMatcher::IsMatching($curRoute["route"], $serverRequest, $routeParams, $methods)) {
                continue;
            }

            return new Route(
                $curRoute["route"],
                $serverRequest->getUri()->getPath(),
                $serverRequest->getMethod(),
                new RouteParams($routeParams),
                $curRoute["call"]
            );
        }
        return new UndefinedRoute($serverRequest->getMethod(), $serverRequest->getUri()->getPath());
    }

}
