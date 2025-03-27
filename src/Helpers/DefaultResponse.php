<?php
namespace App\Helpers;
use App\Http\Client\Clinnet\Api;
use App\Interfaces\ResponseInterface;

class DefaultResponse implements ResponseInterface {
    private $api;
    private $pathname;

    public function __construct(Api $api, $pathname) {
        $this->api = $api;
        $this->pathname = $pathname;
        
    }

    public function createResponse($body = null) {
        return $this->api->post($this->pathname, $body);
    }
}