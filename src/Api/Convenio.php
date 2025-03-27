<?php 

namespace App\Api;

use App\Http\Client\Clinnet\Api;
use Exception;

class Convenio
{
    public static string $pathname = '/ws/agendaweb/convenio.php'; 

    public static function get(): array
    {
        $request = ['funcao' => 'getConvenio'];

        try {
            $data = Api::post(self::$pathname, $request);
            return $data['dados'] ?? [];

        } catch (Exception $error) {
            error_log("Erro ao obter convênios: " . $error->getMessage());
            throw $error;
        }
    }
}
