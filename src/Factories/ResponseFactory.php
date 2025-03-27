<?php
namespace App\Factories;
use App\Helpers\DefaultResponse;
use App\Helpers\LightResponse;
use App\Helpers\UniclinicaResponse;
// Factory para criar a resposta adequada
class ResponseFactory {
    public static function create($client, $apiService, $pathname): DefaultResponse|LightResponse|UniclinicaResponse {
        switch ($client) {
            case 'LIGHT':
                return new LightResponse();
            case 'UNICLINICA':
                return new UniclinicaResponse();
            default:
                return new DefaultResponse($apiService, $pathname);
        }
    }
}