<?php

namespace App\Api;

use App\Models\Consulta as ConsultaModel;

class Consulta
{
    public static function getLimitacoesPorConvenio(array $request): array 
    {
        $convenio_id = $request['idConvenio'] ?? null;

        if (!$convenio_id) {
            return [];
        }

        $response = ConsultaModel::where('convenio_id', $convenio_id)->first();

        if (!$response) {
            return [];
        }

        return $response->toArray();
    }

}
