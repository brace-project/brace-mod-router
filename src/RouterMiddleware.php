<?php


namespace Brace\Router;


use Brace\Core\BraceApp;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouterMiddleware implements MiddlewareInterface
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
        $route = $this->app->router
    }
}