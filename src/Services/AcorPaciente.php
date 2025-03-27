<?php

namespace App\Services;

use App\Api\Agenda;
use App\Api\Paciente;
use App\Api\Convenio;
use App\Config\AppConfig;
use App\Helpers\Helper;
use App\Interfaces\PacienteInterface;
use App\Services\ConvenioService;

use Exception;

class AcorPaciente implements PacienteInterface
{
    private Paciente $paciente;
    private Convenio $convenio;
    private ConvenioService $convenioService;
    private JwtService $jwtService;

    private $token;

    public function __construct(Paciente $paciente, Convenio $convenio)
    {
        $this->paciente        = $paciente;
        $this->convenio        = $convenio;
        $this->convenioService = new ConvenioService();
    }

    public function get(array $params): array {
        try {

            return $this->paciente->get($params);

        } catch (Exception $error) {
            return Helper::handleException($error);
        }
    }

    public function byCPF(array $params): array
    {
        try {

            $paciente = $this->paciente->get($params);

            if (isset($paciente['status']) && $paciente['status'] === -1) {
                return $this->handleUserNotExists($params);
            }

            $state = Helper::getDataNotNull($paciente);

            if (isset($state['idPessoa']) && !isset($state['idConvenio'])) {
                return $this->handleUserExistNotConvenio($state);
            }

            if (isset($state['idConvenio']) && $this->convenioService->getConveniosNotAlloweds($state, 'ACOR')) {
                return $this->handleBlockedConvenio($state);
            }
            
            $result = $this->handleValidUser($state);
            
            return $result;

        } catch (Exception $error) {
            return Helper::handleException($error);
        }
    }

    private function handleUserNotExists(array $params): array {
        $redirect = Helper::getRedirect('REDIRECT_PARTICULAR_CONVENIO');
        return [
            'type_code'  => AppConfig::USER_NOT_EXIST,
            'userExists' => false,
            'redirect'   => $redirect,
            'state' => [
                'cpfPaciente'       => $params['cpfPaciente'],
                'idConvenio'        => AppConfig::ID_CONVENIO_PARTICULAR,
                'idPlano'           => AppConfig::ID_PLANO_PARTICULAR,
                'descricaoConvenio' => AppConfig::DESCRICAO_CONVENIO_PARTICULAR
            ]
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

    private function handleValidUser(array $state): array {

        //$data         = Helper::convenioHandleValidation($state, $convenios);
        $convenio                   = $this->convenioService->getById($state['idConvenio']);
        $state['descricaoConvenio'] = $convenio['descricaoConvenio'];

        $modalDetails = Helper::pacienteShowDetails($state);

        return [
            'type_code'  => AppConfig::USER_EXIST_HAS_CONVENIO, 
            'userExists' => true,
            'redirect'   => AppConfig::$redirects['REDIRECT_PARA_ESPECIALIDADES'],
            'modal'      => $modalDetails, 
            'state'      => $state
        ];
    }
}
