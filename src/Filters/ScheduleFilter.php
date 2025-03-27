<?php

namespace App\Filters;

use App\Interfaces\ScheduleFilterInterface;
use App\Interfaces\UnixTimeProviderInterface;

class ScheduleFilter implements ScheduleFilterInterface {
    private $timeProvider;

    public function __construct(UnixTimeProviderInterface $timeProvider) {
        $this->timeProvider = $timeProvider;
    }

    public function filter(array $data, array $daysNotReturn): array {
        
        $data = array_reduce($data, function ($acc, $item) use ($daysNotReturn) {

            $dataAgendaUnix = $this->timeProvider->getUnixTimeFromDate($item['dataAgenda']);
            
            $isOutReturn = true;

            foreach ($daysNotReturn as $dataPrazoRetorno) {

                if ($dataAgendaUnix <= $dataPrazoRetorno) {
                    $isOutReturn = false;
                    break;
                }

            }

            if ($isOutReturn) {
                $acc[] = $item;
            }

            return $acc;
        
        }, []);

        return $data;

    }
}