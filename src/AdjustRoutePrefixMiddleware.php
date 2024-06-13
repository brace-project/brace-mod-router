<?php

namespace Brace\Router;

use Brace\Core\Base\BraceAbstractMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * This middleware is used to adjust the route prefix.
 */
class AdjustRoutePrefixMiddleware extends BraceAbstractMiddleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routePrefix = $this->app->router->getRoutePrefix();
        if ( ! str_starts_with($request->getUri()->getPath(), $routePrefix)) {
            throw new \InvalidArgumentException("Route prefix missmatch: Got '{$request->getUri()->getPath()}', expected to start with '$routePrefix' (Possible misconfiguration in route prefix?)");
        }
        $request = $request->withAttribute("origRequest", $request);
        $request = $request->withUri($request->getUri()->withPath(substr($request->getUri()->getPath(), strlen($routePrefix))));
        return $handler->handle($request);

    }
}
