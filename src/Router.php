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


    public function on(string $route="*", array $methods=["GET", "POST", "PUT", "DELETE"], callable $fn) : self
    {
        $this->routes[] = ["route"=>$route, "methods"=>$methods, "call" => $fn];
        return $this;
    }


    public function onGet(string $route, callable $fn) : self
    {
        $this->routes[] = ["route"=>$route, "methods"=>["GET"], "call" => $fn];
        return $this;
    }

    public function onPost(string $route, callable $fn) : self
    {
        $this->routes[] = ["route"=>$route, "methods"=>["POST"], "call" => $fn];
        return $this;
    }

    public function onPut(string $route, callable $fn) : self
    {
        $this->routes[] = ["route"=>$route, "methods"=>["PUT"], "call" => $fn];
        return $this;
    }

    public function onDelete(string $route, callable $fn) : self
    {
        $this->routes[] = ["route"=>$route, "methods"=>["DELETE"], "call" => $fn];
        return $this;
    }


    /**
     * @param ServerRequestInterface $serverRequest
     * @return Route
     */
    public function _evalRoute(ServerRequestInterface $serverRequest) : Route
    {
        $foundRoute = null;

        foreach ($this->routes as $curRoute) {
            $routeParams = [];
            $routeMatch = "*";
            if (isset ($curRoute["methods"])) {
                if ( ! in_array($serverRequest->getMethod(), $curRoute["methods"])) {
                    continue;
                }
            }
            if (isset($curRoute["route"])) {
                if ( ! RouteMatcher::IsMatching($curRoute["route"], $serverRequest, $routeParams)) {
                    continue;
                }
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