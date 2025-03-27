<?php

namespace App\Controller;

use App\Services\ProfissionalService;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;

class Profissionais extends BaseController
{
    private $profissionalService;

    public function __construct()
    {
        $this->profissionalService = new ProfissionalService();
    }

    public function get(Request $request, Response $response): Response
    {
        try {
            $params = $request->getParsedBody();
            
            if (empty($params)) {
                throw new HttpBadRequestException($request, 'Parâmetros obrigatórios não fornecidos');
            }

            $data = $this->profissionalService->getDias($params);
            return $this->respondWithData($response, $data);
        } catch (Exception $e) {
            return $this->handleException($response, $e);
        }
    }
}
