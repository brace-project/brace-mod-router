<?php


namespace Brace\Router;


use Brace\Core\Base\BraceAbstractMiddleware;
use Brace\Core\Base\CallbackMiddleware;
use Brace\Core\BraceApp;
use Brace\Core\Mw\Next;
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

            $pipe = new Next();
            foreach ($this->app->route->middleware as $mw) {
                if (is_string($mw)) {
                    if ($this->app->isResolvable($mw))
                        $mw = $this->app->resolve($mw);
                    else
                        $mw = phore_di_instantiate($mw, $this->app);
                }
                if ($mw instanceof BraceAbstractMiddleware)
                    $mw->_setApp($this->app);
                $pipe->addMiddleWare($mw);
            }

            $pipe->addMiddleWare(new CallbackMiddleware(
                function (ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface {
                    $controller = $this->app->route->controller;
                    if (is_array($controller) && is_string($controller[0])) {
                        // Handle static definition [class::class, method]
                        $class = phore_di_instantiate($controller[0], $this->app);
                        $controller = \Closure::fromCallable([$class, $controller[1]]);
                    } else if (is_string($controller)) {
                        // Handle pure ClassNames
                        $class = phore_di_instantiate($controller, $this->app);
                        $controller = \Closure::fromCallable([$class, "__invoke"]);
                    }

                    $response = phore_di_call($controller, $this->app);
                    if ($response === null)
                        throw new \InvalidArgumentException("Controller " . phore_debug_type($controller) . " returned null");
                    if ($response instanceof ResponseInterface)
                        return $response;

                    foreach ($this->returnFormatters as $returnFormatter) {
                        if ($returnFormatter->canHandle($response)) {
                            // Use first ReturnFormatter found
                            return $returnFormatter->transform($response);;
                        }
                    }
                    throw new \InvalidArgumentException("Controller " . phore_debug_type($controller) . " returned " . phore_debug_type($response). ". No ReturnFormatter can handle it.");
                }
            ));

            return $pipe->handle($request);

        }

        // Call next handler
        return $handler->handle($request);
    }
}
