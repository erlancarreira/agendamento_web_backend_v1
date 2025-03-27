<?php

namespace App\Middlewares;

use App\Config\AppConfig;
use App\Helpers\Helper;
use App\Http\Client\Clinnet\Api;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface;

class ClientMiddleware implements Middleware
{
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        // Obtém o cliente dos headers
        $client = $_ENV['CLIENT'] ?? '';

        if (!$client) {
            throw new \Exception('CLIENT not set in environment variables.');
        }
        
        $client = strtoupper($client);
        
        // Verifica se as credenciais existem para o cliente fornecido
        if (!isset(AppConfig::$configuracoes[$client])) {
            throw new \Exception("Configurações não encontradas para o cliente: {$client}");
        }

        if (!isset(AppConfig::$configuracoes[$client]['CREDENCIAIS'])) {
            throw new \Exception("Credenciais não encontradas para o cliente: {$client}");
        }

        $credentials = AppConfig::$configuracoes[$client]['CREDENCIAIS'];

        // Define as credenciais na classe Api do Clinnet
        Api::setCredentials($credentials);

        // Atualiza o corpo da requisição com as novas credenciais
        $parsedBody                 = $request->getParsedBody() ?? [];
        $parsedBody['hash']         = $credentials['hash'];
        $parsedBody['aliasEmpresa'] = $credentials['aliasEmpresa'];
        $parsedBody['idUnidade']    = $credentials['idUnidade'];   
        
        $request = $request->withParsedBody($parsedBody);
        
        // Passa a requisição modificada para o próximo middleware ou handler
        return $handler->handle($request);
    }
}
