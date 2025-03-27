<?php
namespace App\Interfaces;
// Interface para as respostas
interface ResponseInterface {
    public function createResponse($body = null);
}