<?php

namespace App\Controller;

use App\Services\AgendaService;
use Exception;
use Slim\Exception\HttpBadRequestException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class Agenda extends BaseController
{
    private AgendaService $agendaService;

    public function __construct(AgendaService $agendaService)
    {
        $this->agendaService = $agendaService;
    }

    public function getConsultaRetornoAtendimentoPaciente(Request $request, Response $response): Response
    {
        try {
            $params = $request->getParsedBody();
            
            if (empty($params)) {
                throw new HttpBadRequestException($request, 'Parâmetros obrigatórios não fornecidos');
            }

            $data = $this->agendaService->getConsultaRetornoAtendimentoPaciente($params);
            return $this->respondWithData($response, $data);
        } catch (Exception $e) {
            return $this->handleException($response, $e);
        }
    }

    public function getHistoricoAgendamentoPaciente(Request $request, Response $response): Response
    {
        try {
            $params = $request->getParsedBody();
            
            if (empty($params)) {
                throw new HttpBadRequestException($request, 'Parâmetros obrigatórios não fornecidos');
            }

            $data = $this->agendaService->getHistoricoAgendamentoPaciente($params);
            return $this->respondWithData($response, $data);
        } catch (Exception $e) {
            return $this->handleException($response, $e);
        }
    }

    public function post(Request $request, Response $response): Response 
    {
        try {

            $params = $request->getParsedBody();
            
            
            $data = $this->agendaService->fazerAgendamento($params);
            return $this->respondWithData($response, $data);
        } catch (Exception $e) {
            return $this->handleException($response, $e);
        }
    }
}
