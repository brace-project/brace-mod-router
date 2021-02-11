<?php


namespace Brace\Router;


use Brace\Core\Base\BraceAbstractMiddleware;
use Brace\Core\BraceApp;
use Brace\Core\ReturnFormatterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouterDispatchMiddleware extends BraceAbstractMiddleware
{

    /**
     * @var ReturnFormatterInterface
     */
    private $returnFormatter;

    public function __construct (ReturnFormatterInterface $returnFormatter = null)
    {
        $this->returnFormatter = $returnFormatter;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = null;
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