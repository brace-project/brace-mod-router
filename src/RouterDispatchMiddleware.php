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
     * @var ReturnFormatterInterface[]
     */
    private $returnFormatters = [];

    /**
     * RouterDispatchMiddleware constructor.
     * @param ReturnFormatterInterface|null $returnFormatter
     */
    public function __construct (array $returnFormatters = [])
    {
        $this->returnFormatters = $returnFormatters;
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

            foreach ($this->returnFormatters as $returnFormatter) {
                if ($returnFormatter->canHandle($response)) {
                    // Use first ReturnFormatter found
                    return $returnFormatter->transform($response);
                }


            }
            throw new \InvalidArgumentException("Controller " . phore_var($this->app->route->controller) . " returned complex result but no ReturnFormatter can handle it.");
        }

        // Call next handler
        return $handler->handle($request);
    }
}