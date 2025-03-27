<?php

namespace App\Api;

use Exception;
// use InvalidArgumentException;
// use Request;


class Consulta
{
    public static function getLimitacoesPorConvenio(array $request): array {
       
        $convenio_id = $request['idConvenio'];

        $data = [];

        if ($convenio_id) {

            $response = \App\Models\Consulta::where('convenio_id', $convenio_id)->first();

            if ($response) {
                $data[] = $response;
            }

        }

        return $data;
        
    }
    
}