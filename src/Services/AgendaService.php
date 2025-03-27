<?php

namespace App\Services;

use App\Api\Agenda;

use App\Config\AppConfig;
use App\Helpers\Helper;
use App\Http\Client\Clinnet\Api;
use App\Models\Consulta;
use App\Providers\UnixTimeProvider;
use App\Filters\ScheduleFilter;

use App\Services\UserService;

use Exception;

class AgendaService
{
    private string $pathname = '/agenda.php';
    private DateManipulator $DateManipulator;

    private DateAvailability $dateAvailability;
    private UserService $userService;

    private Agenda $api;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
        $this->api = new Agenda();
        $this->DateManipulator = new DateManipulator();
        $this->dateAvailability = new DateAvailability();
    }

    public function getConsultaRetornoAtendimentoPaciente(array $request): array {
        return $this->api->getConsultaRetornoAtendimentoPaciente($request);
    } 

    public function getLimitacoesPorConvenio(array $request): array {
       
        $convenio_id = $request['idConvenio'];
        $data = [];

        if ($convenio_id) {
            $result = Consulta::where('convenio_id', $convenio_id)->first();
            if ($result) {
                $data[] = $result;
            }
        }

        return $data;
        
    }

    public function getHistoricoLimitacoes(array $params): array
    { 
       
        $historico  = Agenda::getHistoricoAgendamentoPaciente($params);
        $limitacoes = $this->getLimitacoesPorConvenio($params);

        return [ $historico, $limitacoes ];
        
    }

    // Função principal para obter o limite do convênio
    public function getLimiteConvenio($request) {
        
        $state = ['appoitamments' => [], 'quantidade' => 0];

        $data = $this->getLimitacoesPorConvenio($request);

        if ( count($data) > 0 ) {
            
            $historicSchedules = $data[0];
            $limitConsults     = $data[1]; 

            if (count($limitConsults) > 0) {

                $limitConsult        = $limitConsults[0];
                $convenio_id         = $limitConsult['convenio_id'];
                $state['quantidade'] = $limitConsult['quantidade'];

                foreach ($historicSchedules as $atendimento) {

                    if (strval($convenio_id) === $atendimento['idConvenio']) {

                        $isWithinCurrentMonthDate = Helper::isWithinCurrentMonth($atendimento['dataAgenda']);
                        
                        if ($isWithinCurrentMonthDate->isAfter) {
                            $state['appoitamments'][] = $atendimento;
                        }
                    
                    }
                }

            }
        }

        return $state;
        
    }

    public function getDiasHorariosConsultas(array $params, array $returns): array {
       
        $data           = Agenda::getHorariosConsultaMensal($params);

        $uniqueDays     = Helper::getUniqueDays($data);
        $daysNotReturn  = Helper::getRetornoAtendimentos($returns, $params);

        $timeProvider   = new UnixTimeProvider();
        $scheduleFilter = new ScheduleFilter($timeProvider);

        $filteredData   = $scheduleFilter->filter($uniqueDays, $daysNotReturn);

        
        return $filteredData;

    }

    public function getDiasAgenda(array $params): array {
        
        $returns          = Agenda::getConsultaRetornoAtendimentoPaciente($params);

        $days             = $this->getDiasHorariosConsultas($params, $returns);

        $appoitamentsData = $this->getLimiteConvenio($params);

        $appoitaments     = $appoitamentsData['appoitamments'];

        $quantity         = $appoitamentsData['quantidade'];

        $appointmentDates = $this->DateManipulator->addOneMonthToAppointments($appoitaments);

        $availableDays    = array_reduce($days, function ($acc, $date) use ($appointmentDates, $quantity) {

            $availableDate = $this->dateAvailability->getDateAvailable($date, $appointmentDates, $quantity);

            if ($availableDate) {
                $acc[] = $availableDate;
            }

            return $acc;

        }, []);

        return $availableDays;

    }

    public function getUserAgenda(array $request): array
    {
        try {
            return $this->userService->getUser($request);
        } catch (Exception $error) {
            if ($error->getMessage() === 'Nenhum paciente encontrado.') {
                return [];
            }

            throw $error;
        }
    }

    public function getHistoricoAgendamentoPaciente(array $params): array
    {
        $data = $this->getUserAgenda(['cpfPaciente' => $params['cpfPaciente']]);

        if ($data['status'] === -1) {
            return [];
        }

        $request = $data[0];

        $this->validateRequest($request);

        $body = $this->buildRequestBody($request['idPessoa']);

        if ($this->isLightClient()) {
            return $this->getLightClientResponse();
        }

        $response = Api::post($this->pathname, $body);

        if ($this->isUniclinicaClient() && count($response) === 0) {
            return $this->getUniclinicaClientResponse();
        }

        return $response;
    }

    private function validateRequest(array $request): void
    {
        if (!isset($request['idPessoa']) || empty($request['idPessoa'])) {
            throw new Exception('O parâmetro idPessoa é requerido', 500);
        }
    }

    private function buildRequestBody(string $idPessoa): array
    {
        return [
            'funcao' => 'getHistoricoAgendamentoPaciente',
            'idPessoa' => $idPessoa,
        ];
    }

    private function isLightClient(): bool
    {
        return $_ENV['CLIENT'] === 'LIGHT';
    }

    private function getLightClientResponse(): array
    {
        // REACT_APP_ID_UNIDADE=52
        // REACT_APP_ID_PROCEDIMENTO=368
        // REACT_APP_ID_CONVENIO=70435
        // REACT_APP_ID_PLANO=860
        return [
            'data' => [
                [
                    'dataFutura' => '1',
                    'idPlano' => '52',
                    'idConvenio' => '70435',
                    'descricaoConvenio' => 'Particular',
                    'redirect' => '/especialidade',
                ],
            ],
        ];
    }

    private function isUniclinicaClient(): bool
    {
        return $_ENV['CLIENT'] === 'UNICLINICA';
    }

    private function getUniclinicaClientResponse(): array
    {
        return [
            'data' => [
                [
                    'dataFutura' => '1',
                    'idPlano' => '',
                    'idConvenio' => '',
                    'redirect' => '/particular-ou-convenio',
                ],
            ],
        ];
    }

    public function fazerAgendamento(array $params): array
    {
        try {

            // Recupera as informações do usuário e caso esteja em produção cria se não existir
            $request = $this->userService->createIfNotExist($params);

            $userExist = $request['userExists'];

            // Configura a observação do agendamento
            $request['observacaoAgendamento'] = $this->getObsByClient($userExist);

            // Faz o agendamento se for produção
            $response = $this->api->post($request);
        
            if (isset($response['status']) && $response['status'] === -1) {
                throw new Exception(json_encode($this->handleErrorAgendamento($request)), 400);
            }

            return $request;

        } catch (Exception $error) {
            
            throw new Exception($error->getMessage(), $error->getCode());
        }
    }

    private function getObsByClient(bool $userExist): string
    {
        $client = $_ENV['CLIENT'];

        $observacoes = [
            'UNICLINICA' => $userExist ? 'Primeiro agendamento na Unicliníca BH: NÃO' : 'Primeiro agendamento na Unicliníca BH: SIM',
        ];

        return $observacoes[$client] ?? '';
    }

    private function handleErrorAgendamento(array $params): array {
        $redirect = Helper::getRedirect('REDIRECT_CENTRAL_AGENDAMENTO');
        return [
            'type_code'  => AppConfig::ERROR_AGENDAMENTO,
            'userExists' => true,
            'redirect'   => $redirect,
            'state' => [
                'cpfPaciente'           => $params['cpfPaciente'],
                'nomePessoa'            => $params['nomePessoa'],
                'idPlano'               => $params['idPlano'],
                'idAgenda'              => $params['idAgenda'],
                'idPessoa'              => $params['idPessoa'],
                "idProcedimento"        => $params['idProcedimento'],
                "observacaoAgendamento" => $params['observacaoAgendamento'],
                "idEspecialidade"       => $params['idEspecialidade'],
            ]
        ];
    }

}
