<?php 
namespace App\Api;
use App\Http\Client\Clinnet\Api;
use App\Validators\User\createValidator;
use Exception;

class Pessoa
{
    public static $pathname = '/ws/agendaweb/pessoa.php'; 

    public static function get(array $params): array
    {
        $funcao = 'getPessoaFisica';
        $request = array_merge($params, ['funcao' => $funcao]);

        $body = array_merge($request, [
            'cpfPaciente' => preg_replace('/[^0-9]/', '', $params['cpfPaciente'])
        ]);

        try {
            
            $data = Api::post(self::$pathname, $body); 

            if (isset($data['status']) && $data['status'] === -1) {
                return $data;
            }
            
            if (isset($data['dados']) && count($data['dados']) > 0) {
                return $data['dados'][0];
            }

            return [];

        } catch (Exception $error) {
            // Log the error if necessary
            throw $error;
        }
    }

    public static function post(array $params): array
    {
        $funcao = 'postPessoaFisica';
       
        $validator = new createValidator();

        $validator->validate($params);
        
        $body = array_merge($params, [
            'funcao' => $funcao, 
            'cpfPaciente' => preg_replace('/[^0-9]/', '', $params['cpfPaciente']),
            'numeroCelularPaciente' => preg_replace('/[^0-9]/', '', $params['numeroCelularPaciente'])
        ]);

        try {
            return Api::post(self::$pathname, $body);
        } catch (Exception $error) {
            // Log the error if necessary
            throw $error;
        }
    }
}