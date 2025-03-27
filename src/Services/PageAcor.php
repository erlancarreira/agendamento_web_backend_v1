<?php

namespace App\Services;

use App\Api\Paciente;
use App\Config\AppConfig;

use App\Helpers\Helper;

use Exception;

class PageAcor
{
    private Paciente $paciente;
    
    public function __construct(Paciente $paciente)
    {
        $this->paciente = $paciente;
    }

    public function get(array $params): array {
        try {

            return $this->paciente->get($params);

        } catch (Exception $error) {
            return Helper::handleException($error);
        }
    }

    public function getRedirectByConvenio(array $params): array {

        try {

            $paciente = $this->paciente->get($params);

            if (isset($paciente['status']) && $paciente['status'] === -1) {

                $params['userExists'] = false;

                return $this->handleMessageConvenioPageUserNotExist($params);
            }

            $state = Helper::getDataNotNull($paciente);

            $state['userExists'] = true;

            return $this->handleConvenioPage($state);

        } catch (Exception $error) {
            return Helper::handleException($error);
        }
        
        
    }

    public function handleConvenioPage(array $params): array {
        return $this->handleMessageConvenioPageUserExist($params);
    }

    private function handleMessageConvenioPageUserExist(array $params): array {

        
        $redirect = Helper::getRedirect('REDIRECT_PARA_CONVENIO');

        return [
            'type_code'  => AppConfig::PARTICULAR_CONVENIO_PAGE,
            'userExists' => true,
            'redirect'   => $redirect,
            'state' => [
                'cpfPaciente'       => $params['cpfPaciente'],
                'idConvenio'        => '',
                'descricaoConvenio' => '',
                'idPlano'           => AppConfig::ID_PLANO_PARTICULAR,
                
            ]
        ];
    }

    private function handleMessageConvenioPageUserNotExist(array $params): array {

        $fallback   = Helper::getFallbackPrimeiroAgendamento();
        $redirect   = Helper::getRedirect('REDIRECT_CENTRAL_AGENDAMENTO');

        return [
            'type_code'  => AppConfig::PARTICULAR_CONVENIO_PAGE,
            'userExists' => false,
            'redirect'   => $redirect,
            'modal'      => $fallback,
            'state' => [
                'cpfPaciente'       => $params['cpfPaciente'],
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
                return $this->handleUserNotExists($params);
            }

            $state = Helper::getDataNotNull($paciente);

            return $this->handleParticularPage($state);

        } catch (Exception $error) {
            return Helper::handleException($error);
        }
        
        
    }

   

    public function handleParticularPage(array $params): array {

        $userExists = $params['userExists'];

        if ($userExists) {
            return $this->handleMessageParticularExist($params);
        }

        return $this->handleMessageParticularNotExist($params);
        
    }

    private function handleMessageParticularExist(array $params) {

        $redirect = Helper::getRedirect('REDIRECT_PARA_ESPECIALIDADES');
       
        return [
            'type_code'  => AppConfig::PARTICULAR_CONVENIO_PAGE,
            'userExists' => true,
            'redirect'   => $redirect,
            'state' => [
                'cpfPaciente'       => $params['cpfPaciente'],
                'idConvenio'        => AppConfig::ID_CONVENIO_PARTICULAR,
                'idPlano'           => AppConfig::ID_PLANO_PARTICULAR,
                'descricaoConvenio' => AppConfig::DESCRICAO_CONVENIO_PARTICULAR
            ],
        ];

    }

    private function handleMessageParticularNotExist(array $params) {

        
        $fallback = Helper::getFallbackPrimeiroAgendamento();
        $redirect = Helper::getRedirect( 'REDIRECT_CENTRAL_AGENDAMENTO');
       
        return [
            'type_code'  => AppConfig::PARTICULAR_CONVENIO_PAGE,
            'userExists' => false,
            'redirect'   => $redirect,
            'modal'      => $fallback,
            'state' => [
                'cpfPaciente'       => $params['cpfPaciente'],
                'idConvenio'        => AppConfig::ID_CONVENIO_PARTICULAR,
                'idPlano'           => AppConfig::ID_PLANO_PARTICULAR,
                'descricaoConvenio' => AppConfig::DESCRICAO_CONVENIO_PARTICULAR
            ],
        ];
    }

    private function handleUserNotExists(array $state): array {
        $fallback = Helper::getFallbackPrimeiroAgendamento();
        $redirect = Helper::getRedirect('REDIRECT_CENTRAL_AGENDAMENTO');
        
        return [
            'type_code'  => AppConfig::USER_NOT_EXIST,
            'userExists' => false,
            'redirect'   => $redirect,
            'modal'      => $fallback,
            'state'      => $state,
        ];
    }

    private function handleBlockedConvenio(array $state): array {
        $fallback = Helper::getFallback();
        $redirect = Helper::getRedirect('REDIRECT_CENTRAL_AGENDAMENTO');

        return [
            'type_code'  => AppConfig::BLOCK_BY_CONVENIO,
            'userExists' => true,
            'redirect'   => $redirect,
            'modal'      => $fallback,
            'state'      => $state
        ];
    }

    private function handleUserExistNotConvenio(array $state): array {
        $fallback = Helper::getFallback();
        $redirect = Helper::getRedirect('REDIRECT_CENTRAL_AGENDAMENTO');
        return [
            'type_code' => AppConfig::USER_EXIST_NOT_CONVENIO,
            'userExists' => true,
            'redirect'   => $redirect,
            'modal'      => $fallback,
            'state'      => $state
        ];
    }

    private function handleHasAgendamento(array $state): array {
        
        $fallback = Helper::getFallback();
        $redirect = Helper::getRedirect('REDIRECT_CENTRAL_AGENDAMENTO');

        return [
            'type_code'  => AppConfig::USER_HAS_AGENDAMENTO,
            'userExists' => true,
            'redirect'   => $redirect,
            'modal'      => $fallback,
            'state'      => $state
        ];
    }

    private function handleConfirmConvenio(array $state): array {
        
        $fallback = Helper::getFallback();
        $redirect = Helper::getRedirect('REDIRECT_CENTRAL_AGENDAMENTO');

        return [
            'type_code'  => AppConfig::USER_HAS_AGENDAMENTO,
            'userExists' => true,
            'redirect'   => $redirect,
            'modal'      => $fallback,
            'state'      => $state
        ];
    }
}
