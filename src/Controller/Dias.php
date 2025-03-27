<?php

namespace App\Controller;

use App\Services\DiaService;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;

class Dias extends BaseController
{
    private $diaService;

    public function __construct()
    {
        $this->diaService = new DiaService();
    }

    public function get(Request $request, Response $response): Response
    {
        try {
            $params = $request->getParsedBody();
            
            if (empty($params)) {
                throw new HttpBadRequestException($request, 'Parâmetros obrigatórios não fornecidos');
            }

            $data = $this->diaService->getDiasAgenda($params);
            return $this->respondWithData($response, $data);
        } catch (Exception $e) {
            return $this->handleException($response, $e);
        }
    }
}
