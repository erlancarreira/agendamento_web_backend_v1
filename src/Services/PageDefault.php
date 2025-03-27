<?php

namespace App\Services;
use App\Api\Convenio;
use App\Api\Paciente;
use App\Config\AppConfig;
use App\Helpers\Helper;

use Exception;

class PageDefault
{
    private Paciente $paciente;

    public function __construct(Paciente $paciente)
    {
        $this->paciente = $paciente;
    }

    public function getRedirectByConvenio(array $params): array {

        try {
            
            $paciente = $this->paciente->get($params);

            if (isset($paciente['status']) && $paciente['status'] === -1) {
                $params['userExists'] = false;
                return $this->handleUserNotExistOrNotConvenio($params);
            }
            
            $state = Helper::getDataNotNull($paciente);
            $state['userExists'] = true;
            return $this->handleConvenioPage($state);
        
        } catch (Exception $error) {
            return Helper::handleException($error);
        }
    }

    private function handleConvenioPage(array $state): array {

        if ($state['userExists'] && isset($state['idConvenio']) && !empty($state['idConvenio'])) {
            return $this->handleUserExistAndHasConvenio($state);
        }

        return $this->handleUserNotExistOrNotConvenio($state);
    }

    private function handleUserExistAndHasConvenio(array $state): array {

        $redirect = Helper::getRedirect('REDIRECT_PARA_ESPECIALIDADES');

        return [
            'type_code'  => AppConfig::USER_EXIST_HAS_CONVENIO,
            'userExists' => true,
            'redirect'   => $redirect,
            'state'      => $state
        ];
    }

    private function handleUserNotExistOrNotConvenio(array $state): array {
        
        $userExists = $state['userExists'];

        $redirect   = Helper::getRedirect($userExists ? 'REDIRECT_PARA_CONVENIO' : 'REDIRECT_PARA_CADASTRO');

        return [
            'type_code'  => AppConfig::PARTICULAR_CONVENIO_PAGE,
            'userExists' => $userExists,
            'redirect'   => $redirect,
            'state' => [
                'cpfPaciente'       => $state['cpfPaciente'],
                'idConvenio'        => '',
                'descricaoConvenio' => '',
                'idPlano'           => AppConfig::ID_PLANO_PARTICULAR,
                
            ]
        ];
    }

    public function getRedirectByParticular(array $params): array {

        try {

            $paciente = $this->paciente->get($params);

            if (isset($paciente['status']) && $paciente['status'] === -1) {
            
                $params['userExists'] = false;
                
                return $this->handleParticularPage($params);
            }
            
            $state = Helper::getDataNotNull($paciente);

            $state['userExists'] = true;
            
            return $this->handleParticularPage($state);

        } catch (Exception $error) {

            return Helper::handleException($error);

        }
    }

    public function handleParticularPage(array $state): array {

        $userExists = $state['userExists'];
        $redirect   = Helper::getRedirect($userExists ? 'REDIRECT_PARA_ESPECIALIDADES': 'REDIRECT_PARA_CADASTRO');
       
        return [
            'type_code'  => AppConfig::PARTICULAR_CONVENIO_PAGE,
            'userExists' => $userExists,
            'redirect'   => $redirect,
            'state' => [
                'cpfPaciente'       => $state['cpfPaciente'],
                'idConvenio'        => AppConfig::ID_CONVENIO_PARTICULAR,
                'descricaoConvenio' => AppConfig::DESCRICAO_CONVENIO_PARTICULAR,
                'idPlano'           => AppConfig::ID_PLANO_PARTICULAR
            ]
        ];
    }
}