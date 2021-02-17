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
            if ($response === null)
                throw new \InvalidArgumentException("Controller " . phore_debug_type($this->app->route->controller) . " returned null");
            if ($response instanceof ResponseInterface)
                return $response;

            foreach ($this->returnFormatters as $returnFormatter) {
                if ($returnFormatter->canHandle($response)) {
                    // Use first ReturnFormatter found
                    $result = $returnFormatter->transform($response);
                    if ( ! $result instanceof ResponseInterface)
                        throw new \InvalidArgumentException("Return Formatter " . phore_debug_type($returnFormatter) . " returned invalid result: Expected: ResponseInterface Found: " . phore_debug_type($result));
                    return $result;
                }
            }
            throw new \InvalidArgumentException("Controller " . phore_debug_type($this->app->route->controller) . " returned " . phore_debug_type($response). ". No ReturnFormatter can handle it.");
        }

        // Call next handler
        return $handler->handle($request);
    }
}