<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Controller\Web\Error;

use Application\Controller\Common\AbstractWebController;
use Eureka\Kernel\Http\Controller\ErrorControllerInterface;
use Eureka\Kernel\Http\Exception\HttpBadRequestException;
use Eureka\Kernel\Http\Exception\HttpConflictException;
use Eureka\Kernel\Http\Exception\HttpForbiddenException;
use Eureka\Kernel\Http\Exception\HttpMethodNotAllowedException;
use Eureka\Kernel\Http\Exception\HttpNotFoundException;
use Eureka\Kernel\Http\Exception\HttpServiceUnavailableException;
use Eureka\Kernel\Http\Exception\HttpTooManyRequestsException;
use Eureka\Kernel\Http\Exception\HttpUnauthorizedException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ErrorController extends AbstractWebController implements ErrorControllerInterface
{
    public function error(ServerRequestInterface $serverRequest, \Exception $exception): ResponseInterface
    {
        $httpCode = match (true) {
            $exception instanceof HttpBadRequestException         => 400,
            $exception instanceof HttpUnauthorizedException       => 401,
            $exception instanceof HttpForbiddenException          => 403,
            $exception instanceof HttpNotFoundException           => 404,
            $exception instanceof HttpMethodNotAllowedException   => 405,
            $exception instanceof HttpConflictException           => 409,
            $exception instanceof HttpTooManyRequestsException    => 429,
            $exception instanceof HttpServiceUnavailableException => 503,
            default                                               => 500,
        };

        $template = $httpCode < 500 ? '@app/Error/Error4XX.twig' : '@app/Error/Error5XX.twig';

        if ($this->acceptJsonResponse()) {
            $content = $this->getErrorContentJson($httpCode, $exception);
        } else {
            $content = $this->getErrorContentHtml($exception, $template, $httpCode);
        }

        return $this->getResponse($content, $httpCode);
    }

    protected function getErrorContentHtml(
        \Exception $exception,
        string $template,
        int $httpCode
    ): string {
        if (!empty($exception->getPrevious())) {
            $exception = $exception->getPrevious();
        }

        $this->getContext()
            ->add('code', $httpCode)
            ->add('exception', $exception)
            ->add('isDebug', $this->isDebug())
            ->add('isDev', $this->isDev())
        ;

        return $this->render($template);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getErrorContentJson(int $code, \Exception $exception): string
    {
        //~ Ajax response error - JsonApi.org error object format + trace
        $error = [
            'status' => (string) $code,
            'title'  => self::HTTP_CODE_MESSAGES[$code] ?? 'Unknown',
            'code'   => !empty($exception->getCode()) ? (string) $exception->getCode() : '99',
            'detail' => !empty($exception->getMessage()) ? $exception->getMessage() : 'Undefined message',
        ];

        if ($this->isDebug()) {
            $error['trace'] = $exception->getTraceAsString();
        }

        try {
            $content = json_encode($error, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            $content = 'json_encode error (' . $exception->getMessage() . ')';
        }

        return $content;
    }
}
