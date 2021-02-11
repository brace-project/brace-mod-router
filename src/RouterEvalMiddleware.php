<?php


namespace Brace\Router;


use Brace\Core\BraceApp;
use Phore\Di\Container\Producer\DiValue;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouterEvalMiddleware implements MiddlewareInterface
{

    /**
     * @var BraceApp
     */
    protected $app;

    public function __construct (BraceApp $app)
    {
        $this->app = $app;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $this->app->router->_evalRoute($request);
        $this->app->define("route", new DiValue($route));

        // Call next middleware
        return $handler->handle($request);
    }
}