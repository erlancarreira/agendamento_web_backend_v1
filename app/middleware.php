<?php

declare(strict_types=1);

use App\Application\Middleware\SessionMiddleware;
use App\Middlewares\JsonMiddleware;
use App\Middlewares\ClientMiddleware;
use Slim\App;

return function (App $app) {
    $app->add(ClientMiddleware::class  ); // Executa primeiro
    $app->add(SessionMiddleware::class ); // Executa terceiro
    $app->add(JsonMiddleware::class    ); // Executa quarto
};
