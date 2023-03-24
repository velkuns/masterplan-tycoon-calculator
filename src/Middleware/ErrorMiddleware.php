<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Middleware;

use Application\Controller\Web\Error\ErrorController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ErrorMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly ErrorController $controller)
    {
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $response = $handler->handle($request);
        } catch (\Exception $exception) {
            $response = $this->getErrorResponse($request, $exception);
        }

        return $response;
    }

    /**
     * Get Error response.
     *
     * @param ServerRequestInterface $request
     * @param \Exception $exception
     * @return ResponseInterface
     */
    private function getErrorResponse(ServerRequestInterface $request, \Exception $exception): ResponseInterface
    {
        $this->controller->preAction($request);
        $response = $this->controller->error($request, $exception);
        $this->controller->postAction();

        return $response;
    }
}
