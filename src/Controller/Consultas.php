<?php

namespace App\Controller;

use App\Models\Consulta;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;

class Consultas extends BaseController
{
    public function put(Request $request, Response $response, array $args): Response
    {
        try {
            $parsedBody = $request->getParsedBody();
            $store = $parsedBody['limite'] ?? null;

            if (!$store) {
                throw new HttpBadRequestException($request, 'O parâmetro limite deve ser fornecido');
            }

            $result = Consulta::upsert($store, ['convenio_id']);
            return $this->respondWithData($response, $result);
        } catch (Exception $e) {
            return $this->handleException($response, $e);
        }
    }

    public function limitacao(Request $request, Response $response, array $args): Response
    {
        try {
            $parsedBody = $request->getParsedBody();
            $quantidade = $parsedBody['quantidade'] ?? null;
            $convenio_id = $parsedBody['convenio_id'] ?? null;

            if (!$quantidade || !$convenio_id) {
                throw new HttpBadRequestException($request, 'Os parâmetros quantidade e convenio_id são obrigatórios');
            }

            $store = [
                'quantidade' => $quantidade,
                'convenio_id' => $convenio_id
            ];

            $consulta = Consulta::create($store);
            return $this->respondWithData($response, $consulta);
        } catch (Exception $e) {
            return $this->handleException($response, $e);
        }
    }

    public function getLimitacaoConvenio(Request $request, Response $response, array $args): Response
    {
        try {
            $convenio_id = $args['convenio_id'] ?? null;

            if (!$convenio_id) {
                throw new HttpBadRequestException($request, 'O parâmetro convenio_id é obrigatório');
            }

            $result = Consulta::where('convenio_id', $convenio_id)->first();
            $data = $result ? [$result] : [];

            return $this->respondWithData($response, $data);
        } catch (Exception $e) {
            return $this->handleException($response, $e);
        }
        
    }

    public function getLimitacoes(Request $request, Response $response, array $args): Response
    {
        try {
            $result = Consulta::all();
            return $this->respondWithData($response, $result);
        } catch (Exception $e) {
            return $this->handleException($response, $e);
        }
    }
}
