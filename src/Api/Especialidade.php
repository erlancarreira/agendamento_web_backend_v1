<?php 

namespace App\Api;
use App\Http\Client\Clinnet\Api;
use Exception;

class Especialidade
{
    public static $pathname = '/ws/agendaweb/especialidade.php'; 

    public static function get(): array
    {
        try {

            $request = ['funcao' => 'getEspecialidade'];

            $data = Api::post(self::$pathname, $request);
            return $data['dados'] ?? [];

        } catch (Exception $error) {
            // Log the error if necessary
            throw $error;
        }
    }

    
}