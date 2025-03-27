<?php

namespace App\Controller;

use App\Services\ConfigService;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;

class Configuracoes extends BaseController
{
    private ConfigService $configService;

    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
    }

    public function post(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            
            if (empty($data)) {
                throw new HttpBadRequestException($request, 'Dados obrigatórios não fornecidos');
            }

            $result = $this->configService->createConfig($data);
            return $this->respondWithData($response, $result);
        } catch (Exception $e) {
            return $this->handleException($response, $e);
        }
    }

    public function get(Request $request, Response $response): Response
    {
        try {
            $result = $this->configService->getAllConfigs();
            return $this->respondWithData($response, $result);
        } catch (Exception $e) {
            return $this->handleException($response, $e);
        }
    }

    public function getLast(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            
            if (empty($params['tipo'])) {
                throw new HttpBadRequestException($request, 'O parâmetro tipo é obrigatório');
            }

            $result = $this->configService->getLastConfig($params['tipo']);
            return $this->respondWithData($response, $result);
        } catch (Exception $e) {
            return $this->handleException($response, $e);
        }
    }

    public function update(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            
            if (empty($data)) {
                throw new HttpBadRequestException($request, 'Dados obrigatórios não fornecidos');
            }

            $this->configService->updateConfig($data);
            return $this->respondWithData($response, null, 204);
        } catch (Exception $e) {
            return $this->handleException($response, $e);
        }
    }
}
