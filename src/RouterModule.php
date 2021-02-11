<?php


namespace Brace\Router;


use Brace\Core\BraceApp;
use Brace\Core\BraceModule;
use Phore\Di\Container\Producer\DiValue;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouterModule implements BraceModule, MiddlewareInterface
{

    public function register(BraceApp $app)
    {
        $app->define("router", new DiValue(new Router()));
        $app->addMiddleware()
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // TODO: Implement process() method.
    }
}