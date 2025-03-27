<?php

namespace App\Services;

use App\Api\Agenda;
use App\Factories\ScheduleFilterFactory;
use App\Helpers\Helper;
use App\Api\Consulta;

class DiaService
{
    private DateManipulator $DateManipulator;
    private DateAvailability $dateAvailability;
    
    public function __construct()
    {
        $this->DateManipulator = new DateManipulator();
        $this->dateAvailability = new DateAvailability();
    }

    public function getHistoricoLimitacoes(array $params): array
    { 
        $historico  = Agenda::getHistoricoAgendamentoPaciente($params);
        $limitacoes = Consulta::getLimitacoesPorConvenio($params);
        return [$historico, $limitacoes];
    }

    /**
     * Obtém o limite de consultas do convênio e seus agendamentos
     * @param array $request Parâmetros da requisição
     * @return array ['appointments' => array, 'quantidade' => int]
     */
    public function getLimiteConvenio(array $request): array 
    {
        $state = [
            'appointments' => [],
            'quantidade'   => 0
        ];

        $data = $this->getHistoricoLimitacoes($request);
        
        if (count($data) === 0) {
            return $state;
        }

        [ $historicoAtendimentos, $limitConsult ] = $data;

        if (count($limitConsult) === 0) {
            return $state;
        }

        $convenioId          = $limitConsult['convenio_id'];
        $state['quantidade'] = $limitConsult['quantidade'];

        foreach ($historicoAtendimentos as $atendimento) {

            if (!isset($atendimento['idConvenio'], $atendimento['dataAgenda'])) {
                continue;
            }

            if (strval($convenioId) !== $atendimento['idConvenio']) {
                continue;
            }

            $isWithinCurrentMonthDate = Helper::isWithinCurrentMonth($atendimento['dataAgenda']);

            if ($isWithinCurrentMonthDate->isAfter) {
                $state['appointments'][] = $atendimento;
            }

        }

        return $state;
    }

    public function getDiasHorariosConsultas(array $params, array $returns): array 
    {
        $data           = Agenda::getHorariosConsultaMensal($params);
        $uniqueDays     = Helper::getUniqueDays($data);
        $daysNotReturn  = Helper::getRetornoAtendimentos($returns, $params);
        $scheduleFilter = ScheduleFilterFactory::create();
        $filteredData   = $scheduleFilter->filter($uniqueDays, $daysNotReturn);
        
        return $filteredData;
    }

    public function getDiasAgenda(array $params): array 
    {
        $returns          = Agenda::getConsultaRetornoAtendimentoPaciente($params);
        $days             = $this->getDiasHorariosConsultas($params, $returns);
        $appointmentsData = $this->getLimiteConvenio($params);

        $appointments     = $appointmentsData['appointments'];
        $quantity         = $appointmentsData['quantidade'];

        $appointmentDates = $this->DateManipulator->addOneMonthToAppointments($appointments);

        return array_reduce($days, function ($acc, $date) use ($appointmentDates, $quantity) {
            $availableDate = $this->dateAvailability->getDateAvailable($date, $appointmentDates, $quantity);
            if ($availableDate) {
                $acc[] = $availableDate;
            }
            return $acc;
        }, []);
    }
}
