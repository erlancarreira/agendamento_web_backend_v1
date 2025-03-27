<?php
namespace App\Helpers;
use App\Interfaces\ResponseInterface;
// Implementação para a resposta LIGHT
class LightResponse implements ResponseInterface {
    public function createResponse($body = null) {
        return [
            [
                'dataFutura'        => "1",
                'idPlano'           => $body['idPlano'],
                'idConvenio'        => $body['idConvenio'],
                'descricaoConvenio' => "Particular",
                'redirect'          => '/especialidade'
            ]
        ];
    }
}