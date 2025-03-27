<?php

namespace App\Controller;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class OptionsCors extends BaseController
{
    public function index(Request $request, Response $response): Response 
    {
        try {
            // CORS Pre-Flight OPTIONS Request Handler
            return $this->respondWithData($response, null, 200);
        } catch (Exception $e) {
            return $this->handleException($response, $e);
        }
    } 
}
