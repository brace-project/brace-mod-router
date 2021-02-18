<?php


namespace Brace\Router;



use Psr\Http\Message\ServerRequestInterface;

class RouteMatcher
{
    public static function IsMatching (string $routeDef, ServerRequestInterface $request, &$params, &$methods) : bool
    {

        $parts = explode("@", $routeDef, 2);
        if (count ($parts) !== 2) {
            throw new \InvalidArgumentException("Invalid route definition: '$routeDef': Proper syntax: GET|POST|PUT|DELETE@/route/:param/target");
        }
        $methods = explode("|", $parts[0]);
        $route = $parts[1];
        
        array_filter($methods, function (string $method) use ($routeDef) {
            if ( ! in_array($method, ["GET", "PUT", "DELETE", "POST", "HEADER", ""]))
                throw new \InvalidArgumentException("Route definition invalid: '$routeDef' includes invalid request method: '$method' (Allowed: POST|GET|HEADER|PUT|DELETE)");
            return true;
        });

        if ( ! str_starts_with($route, "/"))
            throw new \InvalidArgumentException("Route definition invalid: '$routeDef': Route must start with slash /");

        if ( ! in_array($request->getMethod(), $methods) && $methods[0] !== "")
            return false;

        $route = preg_replace("|\\*|", '.*', $route);
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