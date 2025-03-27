<?php

namespace App\Controller;

use App\Services\ConvenioService;
use App\Models\Convenio;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;

class Convenios extends BaseController
{
    private $convenioService;

    public function __construct()
    {
        $this->convenioService = new ConvenioService();
    }
    public function get(Request $request, Response $response): Response 
    {
        try {
            $params = $request->getParsedBody();
            
            if (empty($params)) {
                throw new HttpBadRequestException($request, 'Parâmetros obrigatórios não fornecidos');
            }

            $data = $this->convenioService->get($params);
            return $this->respondWithData($response, $data);
        } catch (Exception $e) {
            return $this->handleException($response, $e);
        }
    }
    public function getWithLimit(Request $request, Response $response): Response
    {
        try {
            // Buscar dados dos convênios com limite
            $convenios = Convenio::with('limite')->get();
            return $this->respondWithData($response, $convenios);
        } catch (Exception $e) {
            return $this->handleException($response, $e);
        }
    }

}
