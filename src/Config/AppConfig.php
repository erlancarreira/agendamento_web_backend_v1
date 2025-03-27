<?php
namespace App\Config;
// Definindo constantes de configuração
class AppConfig {

    const USER_NOT_EXIST           = 'USER_NOT_EXIST';
    const BLOCK_BY_CONVENIO        = 'BLOCK_BY_CONVENIO';
    const USER_EXIST_HAS_CONVENIO  = 'USER_EXIST_HAS_CONVENIO';
    const USER_EXIST_NOT_CONVENIO  = 'USER_EXIST_NOT_CONVENIO';
    const USER_HAS_AGENDAMENTO     = 'USER_HAS_AGENDAMENTO';
    const USER_CONFIRM_CONVENIO    = 'USER_CONFIRM_CONVENIO';
    const ERROR_AGENDAMENTO        = 'ERROR_AGENDAMENTO';
    const PARTICULAR_CONVENIO_PAGE = 'PARTICULAR_CONVENIO_PAGE';
    const PRIMEIRO_AGENDAMENTO     = 'PRIMEIRO_AGENDAMENTO';
    

    const REDIRECT_PARA_CADASTRO       = '/cadastro';
    const REDIRECT_PARA_CONVENIO       = '/convenio';
    const REDIRECT_PARTICULAR_CONVENIO = '/particular-ou-convenio';
    const REDIRECT_CENTRAL_AGENDAMENTO = '/central-agendamento';

    const REDIRECT_PARA_ESPECIALIDADES = '/especialidade';
    const ID_CONVENIO_PARTICULAR = 2;
    const ID_PLANO_PARTICULAR = 1;
    const DESCRICAO_CONVENIO_PARTICULAR = 'Particular';

    const CONVENIOS_BLOQUEADOS=["58489"]; // ACOR

    const GET_CONVENIOS_PERMITIDOS=["61827", "60727"]; // UNICLINICA

    const ESPECIALIDADES_NAO_PERMITIDAS=["61827", "60727"]; // UNICLINICA

    // Adiciona um mapa de redirecionamentos ou fallbacks, se necessário
    public static $redirects = [
        'REDIRECT_PARA_CADASTRO'       => self::REDIRECT_PARA_CADASTRO,
        'REDIRECT_PARA_CONVENIO'       => self::REDIRECT_PARA_CONVENIO,
        'REDIRECT_PARTICULAR_CONVENIO' => self::REDIRECT_PARTICULAR_CONVENIO,
        'REDIRECT_CENTRAL_AGENDAMENTO' => self::REDIRECT_CENTRAL_AGENDAMENTO,
        'REDIRECT_PARA_ESPECIALIDADES' => self::REDIRECT_PARA_ESPECIALIDADES,
    ];


    public static $configuracoes = [
        'ACOR' => [
            "CREDENCIAIS" => [
                "aliasEmpresa" => "ACOR",
                "hash"         => "ad655c0a14276899ce6b401843115d18",
                "idUnidade"    => 1,
            ],
            "QUANTIDADE_HORARIOS_EXIBIR"    => 3,  
            "CONVENIOS_BLOQUEADOS"          => [ "58489" ],
            'ESPECIALIDADES_EXCLUIDAS'      => [ 
                "49", "85", 
                "32", "12", "19", 
                "35", "21", "6", 
                "24", "8" 
            ],
            'ESPECIALIDADES_BLOQUEADAS'     => [ "39", "46", "30", "28"],
            'ESPECIALIDADES_EM_DESTAQUE'    => [ "3" , "33", "7", "10", "18", "25", "29", "17" ],
            'ULTIMAS_ESPECIALIDADES'        => [ "46", "39" ],
        ],
        'UNICLINICA' => [
            "CREDENCIAIS" => [
                "aliasEmpresa" => "UNICLINICA",
                "hash"         => "ff7a0eab6fa38502d8a562f87fd85e36",
                "idUnidade"    => 1,
            ],
            "QUANTIDADE_HORARIOS_EXIBIR"    => 3,

            "CONVENIOS_BLOQUEADOS"          => [ ],    
                    
            'ESPECIALIDADES_EXCLUIDAS'      => [ "9","62", "18", "54", "29", "56", "55" ],
            'ESPECIALIDADES_BLOQUEADAS'     => [],
            'ESPECIALIDADES_EM_DESTAQUE'    => [ "13","4","25","15","22","24","17" ],
            'ULTIMAS_ESPECIALIDADES'        => [],
        ],
    ];
}