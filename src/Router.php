<?php


namespace Brace\Router;


use Brace\Router\Type\Route;
use Brace\Router\Type\RouteParams;
use Brace\Router\Type\UndefinedRoute;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

class Router
{

    private $routes = [];

    public function delegate(string $route, string $className, array $mw=[]) : self
    {
        $this->routes[] = ["route"=>$route, "delegate"=>$className, "mw" => $mw];
        return $this;
    }


    /**
     * @param string $route
     * @param callable|class-string $fn
     * @param MiddlewareInterface[]|class-string[] $mw
     * @return $this
     */
    public function on(string $route, callable|string $fn, array $mw = []) : self
    {
        if (is_string($fn) && ! class_exists($fn))
            throw new \InvalidArgumentException("Parameter 2 must be Closure or class-string: '$fn' class not existing");

        $this->routes[] = ["route"=>$route, "call" => $fn, "mw" => $mw];
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
                $curRoute["call"],
                $curRoute["mw"]
            );
        }
        return new UndefinedRoute($serverRequest->getMethod(), $serverRequest->getUri()->getPath());
    }

}
