<?php

declare(strict_types=1);
use App\Controller\Agenda;

use App\Controller\Dias;
use App\Controller\Page;
use App\Controller\Profissionais;
use Slim\Routing\RouteCollectorProxy;

use Slim\App;
use App\Controller\Consultas;
use App\Controller\Convenios;
use App\Controller\Especialidades;
use App\Controller\Configuracoes;

use App\Controller\Paciente;
use App\Middlewares\JwtMiddleware;


return function (App $app) {

    $app->group('/v1', function (RouteCollectorProxy $group) {
        // Rota pública para geração do token
        $group->post('/paciente/cpf', [ Paciente::class, 'byCPF' ]);

        // Grupo de rotas protegidas
        $group->group('', function (RouteCollectorProxy $group) {
            $group->put('/consultas'                        , [ Consultas::class     , 'put']);
            $group->post('/consultas/limitacao'             , [ Consultas::class     , 'limitacao']);
            $group->get('/consultas/limitacao'              , [ Consultas::class     , 'getLimitacoes']);
            $group->get('/consultas/limitacao/{convenio_id}', [ Consultas::class     , 'getLimitacaoConvenio']);
            $group->get('/convenios'                        , [ Convenios::class     , 'get' ]);
            $group->get('/especialidades'                   , [ Especialidades::class, 'get' ]);
            $group->put('/especialidades'                   , [ Especialidades::class, 'put' ]);
            $group->post('/dias'                            , [ Dias::class          , 'get' ]);
            $group->post('/profissionais'                   , [ Profissionais::class , 'get'  ]);
            $group->post('/agenda'                          , [ Agenda::class        , 'post'  ]);
            $group->post('/configuracoes'                   , [ Configuracoes::class , 'post']);
            $group->get('/configuracoes'                    , [ Configuracoes::class , 'get']);
            $group->get('/configuracoes/last'               , [ Configuracoes::class , 'getLast']);
            $group->put('/configuracoes'                    , [ Configuracoes::class , 'update']);
            $group->get('/paciente'                         , [ Paciente::class      , 'get' ]);
            $group->post('/paciente/dados'                  , [ Paciente::class      , 'byCPF' ]);
            $group->post('/page/particular'                 , [ Page::class          , 'getRedirectByParticular' ]); 
            $group->post('/page/convenio'                   , [ Page::class          , 'getRedirectByConvenio' ]); 
        })->add(JwtMiddleware::class);
    });
};
