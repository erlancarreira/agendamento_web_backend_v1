<?php
namespace App\Helpers;
use App\Interfaces\ResponseInterface;
// Implementação para a resposta UNICLINICA
class UniclinicaResponse implements ResponseInterface {
    public function createResponse($body = null) {
        return [
            [
                'dataFutura' => "1",
                'idPlano'    => '',
                'idConvenio' => '',
                'redirect'   => "/particular-ou-convenio"
            ]
        ];
    }
}