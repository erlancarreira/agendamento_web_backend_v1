<?php

namespace App\Controller;

use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;
use Exception;

abstract class BaseController
{
    protected function respondWithData(
        Response $response,
        $data = null,
        int $statusCode = 200
    ): Response {
        $payload = new ActionPayload($statusCode, $data);

        return $this->respond($response, $payload);
    }

    protected function handleException(
        Response $response,
        Exception $exception
    ): Response {
        if ($exception instanceof HttpNotFoundException) {
            $error = new ActionError(
                'RESOURCE_NOT_FOUND',
                'O recurso solicitado não foi encontrado.'
            );
            $payload = new ActionPayload(404, null, $error);
        } elseif ($exception instanceof HttpBadRequestException) {
            $error = new ActionError(
                'BAD_REQUEST',
                'Requisição inválida: ' . $exception->getMessage()
            );
            $payload = new ActionPayload(400, null, $error);
        } else {
            $error = new ActionError(
                'SERVER_ERROR',
                'Ocorreu um erro interno no servidor. ' . $exception->getMessage()
            );
            $payload = new ActionPayload(500, null, $error);
        }

        return $this->respond($response, $payload);
    }

    private function respond(
        Response $response,
        ActionPayload $payload
    ): Response {
        $json = json_encode($payload, JSON_PRETTY_PRINT);
        $response->getBody()->write($json);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($payload->getStatusCode());
    }
}
