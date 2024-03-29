<?php


namespace Brace\Router\Type;

use Brace\Core\Helper\Immutable;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Class Route
 * @package Brace\Router\Type
 *
 * @property string $routeOrig      The original Route
 * @property string $requestMethot  The current request method (POST, GET, DELETE, OPTIONS)
 * @property string $requestPath    The current Request Path
 * @property RouteParams $routeParams
 * @property callable|null $controller      The Controller to call
 * @property MiddlewareInterface[]|class-string[] $middleware
 */
class Route extends Immutable
{



    public function isDefined() : bool
    {
        return true;
    }

    /**
     * Route constructor.
     * @param $routeOrig
     * @param $requestPath
     * @param $requestMethod
     * @param $methodOrig
     * @param RouteParams $routeParams
     * @param $controller
     */
    public function __construct($routeOrig, $requestPath, $requestMethod, RouteParams $routeParams, $controller, array $middleware)
    {
        parent::__construct([
            "routeOrig" => $routeOrig,
            "requestMethod" => $requestMethod,
            "requestPath" => $requestPath,
            "routeParams" => $routeParams,
            "controller" => $controller,
            "middleware" => $middleware
        ]);
    }

}