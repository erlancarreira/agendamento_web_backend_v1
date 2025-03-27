<?php

namespace App\Controller;

use App\Factories\PageFactory;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;

class Page extends BaseController
{
    public function getRedirectByConvenio(Request $request, Response $response, array $args): Response
    {
        try {
            $params = $request->getParsedBody();

            $pageService = PageFactory::create($_ENV['CLIENT']);
            $data = $pageService->getRedirectByConvenio($params);

            return $this->respondWithData($response, $data);
        } catch (Exception $e) {
            return $this->handleException($response, $e);
        }
    }

    public function getRedirectByParticular(Request $request, Response $response, array $args): Response
    {
        try {
            $params = $request->getParsedBody();
            
            $pageService = PageFactory::create($_ENV['CLIENT']);
            $data = $pageService->getRedirectByParticular($params);

            return $this->respondWithData($response, $data);
        } catch (Exception $e) {
            return $this->handleException($response, $e);
        }
    }
}
