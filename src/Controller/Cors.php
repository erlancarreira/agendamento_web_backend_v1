<?php

namespace App\Controller;

use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;

class Cors extends BaseController
{
    public function index(Request $request, RequestHandlerInterface $handler): Response 
    {
        try {
            $routeContext = RouteContext::fromRequest($request);
            $routingResults = $routeContext->getRoutingResults();
            $methods = $routingResults->getAllowedMethods();
            $requestHeaders = $request->getHeaderLine('Access-Control-Request-Headers');

            $response = $handler->handle($request);

            $response = $response
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Methods', implode(',', $methods))
                ->withHeader('Access-Control-Allow-Headers', $requestHeaders)
                // Optional: Allow Ajax CORS requests with Authorization header
                ->withHeader('Access-Control-Allow-Credentials', 'true');

            return $response;
        } catch (Exception $e) {
            // Em caso de erro, ainda retornamos os headers CORS básicos
            $response = new \Slim\Psr7\Response();
            $response = $response
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Methods', 'GET,POST,PUT,DELETE,OPTIONS')
                ->withHeader('Access-Control-Allow-Headers', 'Content-Type,Authorization');

            return $this->handleException($response, $e);
        }
    }
}
