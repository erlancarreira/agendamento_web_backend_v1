<?php

namespace App\Middlewares;

use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use App\Services\JwtService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Psr7\Response as SlimResponse;

class JwtMiddleware implements MiddlewareInterface
{
    private JwtService $jwtService;

    public function __construct()
    {
        $this->jwtService = new JwtService();
    }

    private function createErrorResponse(string $type, string $message, int $status = 401): Response 
    {
        $response = new SlimResponse();
        $error = new ActionError($type, $message);
        $payload = new ActionPayload($status, null, $error);
        
        $response->getBody()->write(json_encode($payload));
        return $response->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
    }

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        try {

            $token = $request->getHeaderLine('Authorization');
            
            if (empty($token)) {
                return $this->createErrorResponse(
                    'UNAUTHENTICATED',
                    'Token não fornecido'
                );
            }

            // Remove o prefixo "Bearer " se existir
            $token = str_replace('Bearer ', '', $token);

            $tokenData = $this->jwtService->validateToken($token);
            
            if (empty($tokenData['hash'])) {
                throw new HttpBadRequestException($request, 'Token inválido ou expirado');
            }

            $request = $request->withAttribute('token', $tokenData['hash']);

            return $handler->handle($request);

        } catch (\InvalidArgumentException $e) {
            return $this->createErrorResponse(
                'BAD_REQUEST',
                'Token mal formatado',
                400
            );
        } catch (\Exception $e) {
            // Pega o código de status da exceção ou usa 500 como padrão
            $status = $e->getCode() ?: 500;
            
            return $this->createErrorResponse(
                $status === 401 ? 'UNAUTHENTICATED' : 'SERVER_ERROR',
                $e->getMessage(),
                $status
            );
        }
    }
}
