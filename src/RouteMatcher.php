<?php


namespace Brace\Router;



use Psr\Http\Message\ServerRequestInterface;

class RouteMatcher
{
    public static function IsMatching (string $routeDef, ServerRequestInterface $request, &$params, &$methods) : bool
    {

        [$methodDef, $route] = explode(":", $routeDef, 2);
        $methods = explode("|", $methodDef);
        $methods = array_filter($methods, function (string $method) use ($routeDef) {
            if ( ! in_array($method, ["GET", "PUT", "DELETE", "POST", "HEADER"]))
                throw new \InvalidArgumentException("Route definition invalid: '$routeDef' includes invalid request method: '$method' (Allowed: POST|GET|HEADER|PUT|DELETE)");
            return $method;
        });

        if ( ! str_starts_with($route, "/"))
            throw new \InvalidArgumentException("Route definition invalid: '$routeDef': Route must start with slash /");

        if ( ! in_array($request->getMethod(), $methods))
            return false;

        $route = preg_replace("|\\*|", '.*', $routeDef);
        $route = preg_replace("|::([a-zA-Z0-9_]+)|", '(?<$1>.*)', $route);
        $route = preg_replace("|/:([a-zA-Z0-9_]+)\\?|", '(/(?<$1>[^/]*))?', $route);
        $route = preg_replace("|:([a-zA-Z0-9_]+)|", '(?<$1>[^/]*)', $route);

        $path = $request->getUri()->getPath();
        if(preg_match("|^" . $route . "$|", $path, $params)) {
            foreach ($params as $key => $val) {
                if ($val == "") {
                    unset($params[$key]);
                }
            }
            return true;
        }
        return false;
    }
}