<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpNotImplementedException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;
use Throwable;

class HttpErrorHandler extends SlimErrorHandler
{
    private function getStatusCode(Throwable $exception): int 
    {
        if ($exception instanceof HttpException) {
            return (int) $exception->getCode();
        }
        return 500;
    }

    private function getErrorType(Throwable $exception): string 
    {
        if ($exception instanceof HttpNotFoundException) {
            return ActionError::RESOURCE_NOT_FOUND;
        }
        if ($exception instanceof HttpMethodNotAllowedException) {
            return ActionError::NOT_ALLOWED;
        }
        if ($exception instanceof HttpUnauthorizedException) {
            return ActionError::UNAUTHENTICATED;
        }
        if ($exception instanceof HttpForbiddenException) {
            return ActionError::INSUFFICIENT_PRIVILEGES;
        }
        if ($exception instanceof HttpBadRequestException) {
            return ActionError::BAD_REQUEST;
        }
        if ($exception instanceof HttpNotImplementedException) {
            return ActionError::NOT_IMPLEMENTED;
        }
        return ActionError::SERVER_ERROR;
    }

    private function getErrorDescription(Throwable $exception): string 
    {
        if ($exception instanceof HttpException) {
            return $exception->getMessage();
        }
        
        if ($this->displayErrorDetails && $exception instanceof Throwable) {
            return $exception->getMessage();
        }
        
        return 'Internal Server Error';
    }

    protected function respond(): Response
    {
        $exception = $this->exception;
        $statusCode = $this->getStatusCode($exception);
        $error = new ActionError(
            $this->getErrorType($exception),
            $this->getErrorDescription($exception)
        );
        

        $payload = new ActionPayload($statusCode, null, $error);
        
        $encodedPayload = json_encode($payload);
        if ($encodedPayload === false) {
            $error = new ActionError(ActionError::SERVER_ERROR, 'JSON encoding failed');
            $payload = new ActionPayload(500, null, $error);
            $encodedPayload = json_encode($payload);
        }

        $response = $this->responseFactory->createResponse($statusCode);
        $response->getBody()->write($encodedPayload);

        return $response->withHeader('Content-Type', 'application/json');
    }
}
