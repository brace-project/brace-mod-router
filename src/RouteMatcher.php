<?php


namespace Brace\Router;



use Psr\Http\Message\ServerRequestInterface;

class RouteMatcher
{
    public static function IsMatching (string $routeDef, ServerRequestInterface $request, &$params) : bool
    {
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