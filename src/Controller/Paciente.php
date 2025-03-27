<?php

namespace App\Controller;

use App\Factories\PacienteFactory;
use App\Services\JwtService;
use Exception;
use Slim\Exception\HttpBadRequestException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class Paciente extends BaseController
{
    private JwtService $jwtService;
    public function __construct() {
        $this->jwtService = new JwtService();
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        try {
            $params = $request->getParsedBody();

            $pacienteService = PacienteFactory::create($_ENV['CLIENT']);
            $data = $pacienteService->get($params);

            return $this->respondWithData($response, $data);
        } catch (Exception $e) {
            return $this->handleException($response, $e);
        }
    }

    public function byCPF(Request $request, Response $response, array $args): Response
    {
        try {
            $params = $request->getParsedBody();
            
            if (empty($params['cpfPaciente'])) {
                throw new HttpBadRequestException($request, 'O parâmetro CPF é obrigatório');
            }

            $pacienteService = PacienteFactory::create($_ENV['CLIENT']);

            // Gera o token JWT apenas com o CPF
            $tokenData = [
                'hash' => $params['hash']
            ];

            $data = $pacienteService->byCPF($params);

            $data['token'] = $this->jwtService->generateToken($tokenData);

            return $this->respondWithData($response, $data);
        } catch (Exception $e) {
            return $this->handleException($response, $e);
        }
    }

    public function cpf(Request $request, Response $response): Response
    {
        try {
            $params = $request->getParsedBody();
            
           
            if (empty($params['cpfPaciente'])) {
                throw new HttpBadRequestException($request, 'O parâmetro cpfPaciente é obrigatório');
            }

            $jwtService = new JwtService();
            $token = $jwtService->generateToken(['cpf' => $params['cpfPaciente']]);

            return $this->respondWithData($response, ['token' => $token]);
        } catch (Exception $e) {
            return $this->handleException($response, $e);
        }
    }

}
