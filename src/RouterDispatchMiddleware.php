<?php


namespace Brace\Router;


use Brace\Core\BraceApp;
use Brace\Core\ReturnFormatterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouterDispatchMiddleware implements MiddlewareInterface
{

    /**
     * @var BraceApp
     */
    private $app;

    /**
     * @var ReturnFormatterInterface
     */
    private $returnFormatter;

    public function __construct (BraceApp $app, ReturnFormatterInterface $returnFormatter = null)
    {
        $this->app = $app;
        $this->returnFormatter = $returnFormatter;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->app->route->isDefined()) {
            $response = phore_di_call($this->app->route->controller, $this->app);
        }
        if ($response !== null) {
            if ($response instanceof ResponseInterface)
                return $response;

            // Return and don't call next handler
            if ($this->returnFormatter === null) {
                throw new \InvalidArgumentException("Controller " . phore_var($this->app->route->controller) . " returned complex result but no ReturnFormatter is defined.");
            }
            return $this->returnFormatter->transform($response);
        }

        // Call next handler
        return $handler->handle($request);
    }
}