<?php

namespace App\Controller;

use App\Services\EspecialidadeService;
use App\Models\Especialidade;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;

class Especialidades extends BaseController
{
    private $especialidadeService;

    public function __construct()
    {
        $this->especialidadeService = new EspecialidadeService();
    }
    public function get(Request $request, Response $response): Response
    {
        try {
            $data = $this->especialidadeService->get();
            return $this->respondWithData($response, $data);
        } catch (Exception $e) {
            return $this->handleException($response, $e);
        }
    }

    public function put(Request $request, Response $response): Response
    {
        try {
            
            $parsedBody = $request->getParsedBody();

            $itens = array_filter($parsedBody, function($key) {
                return $key !== 'aliasEmpresa' && $key !== 'hash';
            }, ARRAY_FILTER_USE_KEY);

            $resultArray = [];

            foreach ($itens as $item) {
                if (!isset($item['especialidade_id']) || !isset($item['active']) || !isset($item['order'])) {
                    throw new HttpBadRequestException($request, 'Campos obrigatórios ausentes no item');
                }

                $values = [
                    "active" => $item['active'],
                    "order" => $item['order']
                ];

                $responseUpdate = Especialidade::where('especialidade_id', $item['especialidade_id'])->update($values);
                $resultArray[] = $responseUpdate;
            }

            return $this->respondWithData($response, $resultArray);
        } catch (Exception $e) {
            return $this->handleException($response, $e);
        }
    }
}
