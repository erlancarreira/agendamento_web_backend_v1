<?php

namespace App\Api;
use App\Builders\RequestBodyBuilder;
use App\Factories\ResponseFactory;
use App\Helpers\Helper;
use App\Http\Client\Clinnet\Api;
use App\Api\Paciente;
use App\Validators\Agenda\agendarValidator;
use App\Validators\ParamsValidator;
use Exception;
// use InvalidArgumentException;
// use Request;


class Agenda
{
    public static $pathname = '/ws/agendaweb/agenda.php'; 

    public static function getHistoricoAgendamentoBody($request) {
        return [
            
            'idPessoa'     => $request['idPessoa'],
            "aliasEmpresa" => $request['aliasEmpresa'],
            "cpfPaciente"  => $request['cpfPaciente'],
            "hash"         => $request['hash'],
            'funcao'       => 'getHistoricoAgendamentoPaciente',
        ];
    }
    public static function getData($data) {
        
        if (isset($data['status']) && $data['status'] === -1) {
            return [];
        } else if (count($data) === 0) {
            return [];
        } else {
            
            return $data[0];
        }
    }
    public static function validate($request) {
        if (!isset($request['idPessoa']) || empty($request['idPessoa'])) {
            throw new Exception(json_encode(['message' => 'O parametro idPessoa é requerido', 'code' => 400], JSON_UNESCAPED_UNICODE), 400);
        }
    }

    public static function get(array $params): array
    {
        try {

            return Agenda::getHorariosConsulta($params);
            
        } catch (Exception $error) {
            // Log the error if necessary
            throw $error;
        }
    }

    public static function getConsultaRetornoAtendimentoPaciente(array $params): array
    {

        if (!isset($params['idPessoa'])) {
            return [];
        }
        
        $method = 'getConsultaRetornoAtendimentoPaciente';
       
        $validator = new ParamsValidator(['idPessoa', 'idUnidade']);
        $validator->validate($params);

        $bodyBuilder = new RequestBodyBuilder($method, $params);
        $body        = $bodyBuilder->build();

        $response = Api::post(self::$pathname, $body);

        if ($response['dados'] && count($response['dados']) > 0) {
            return $response['dados'];
        }

        return [];
    }

    public static function getHorariosConsultaMensal(array $params): array {

        $method = 'getHorariosConsultaMensal';
        
        $validator = new ParamsValidator(['idUnidade', 'idConvenio', 'idPlano', 'idEspecialidade', 'dataNascimento']);

        $validator->validate($params);

        $bodyBuilder = new RequestBodyBuilder($method, $params);
        $body = $bodyBuilder->build();

        $response = API::post(self::$pathname, $body);

        if ($response['dados'] && count($response['dados']) > 0) {
            
            return $response['dados'];
        }

        return [];
    }
    
    public static function getHorariosConsulta(array $params): array {

        $funcao = 'getHorariosConsulta';
        
        $request = array_merge($params, ['funcao' => $funcao]);

        $validator = new ParamsValidator(['idUnidade', 'idConvenio', 'idPlano', 'idEspecialidade', 'dataNascimento']);

        $validator->validate($params);

        $body = [
            'funcao'          => 'getHorariosConsulta',
            'idUnidade'       => $request['idUnidade'],
            'idConvenio'      => $request['idConvenio'],
            'idPlano'         => $request['idPlano'],
            'idEspecialidade' => $request['idEspecialidade'],
            'dataNascimento'  => $request['dataNascimento'],
        ];

        try {

            $data = API::post(self::$pathname, $body);

            if ($data['dados'] && count($data['dados']) > 0) {
                return $data['dados'];
            }

            return [];
            
        } catch (Exception $error) {
            // Log the error if necessary
            throw $error;
        }
    }
    
    public static function getUserAgenda(array $params): array
    {
        try {

            return Paciente::get($params);
            
        } catch (Exception $error) {
            // Log the error if necessary
            throw $error;
        }
    }
    
    public static function getHistoricoAgendamentoPaciente(array $params): array
    {
        try {
            
            
            $client = $params['aliasEmpresa'];
            
            Agenda::validate($params);

            $body            = Agenda::getHistoricoAgendamentoBody($params);

            $apiService      = new Api();
            $responseHandler = ResponseFactory::create($client, $apiService, Agenda::$pathname);
            $response        = $responseHandler->createResponse($body);          

            return $response;
        
        } catch (Exception $error) {
            error_log($error->getMessage() . ' ERROR');
            return ['error' => $error->getMessage(), 'code' => $error->getCode() ];
        }
    }

    public static function post(array $params): bool | array
    {

        $request = [
            'funcao'                => 'postAgendamento',
            'idUnidade'             => $params['idUnidade'],
            'idAgenda'              => $params['idAgenda'],
            'idPessoa'              => $params['idPessoa'],
            'nomePessoa'            => $params['nomePessoa'],
            'idPlano'               => $params['idPlano'],
            'idProcedimento'        => $params['idProcedimento'] ?? 1,
            'observacaoAgendamento' => $params['observacaoAgendamento'],     
            'idEspecialidade'       => $params['idEspecialidade'],
        ];

        $validator = new agendarValidator();
        $validator->validate($request);

        try {

            $response = Api::post(self::$pathname, $request);

            return $response['status'] === 1;

        } catch (Exception $error) {
            // Log the error if necessary
            throw $error;
        }
    }
    
}