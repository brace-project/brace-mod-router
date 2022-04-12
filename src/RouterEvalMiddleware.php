<?php


namespace Brace\Router;


use Brace\Core\Base\BraceAbstractMiddleware;
use Brace\Core\BraceApp;
use Phore\Di\Container\Producer\DiValue;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouterEvalMiddleware extends BraceAbstractMiddleware
{

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $this->app->router->_evalRoute($request);
        $this->app->define("route", new DiValue($route));
        $this->app->define("routeParams", new DiValue($route->routeParams));

        foreach ($route->middleware as $mw) {
            if (is_string($mw)) {
                if ($this->app->isResolvable($mw))
                    $mw = $this->app->resolve($mw);
                else
                    $mw = phore_di_instantiate($mw, $this->app);
            }
            $this->app->pipe->addMiddleWare($mw);
        }

        // Call next middleware
        return $handler->handle($request);
    }
}
