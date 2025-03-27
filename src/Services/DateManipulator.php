<?php

namespace App\Services;

use App\Interfaces\DateManipulatorInterface;
use DateTime;

class DateManipulator implements DateManipulatorInterface {

    public function addMonthsToDate(DateTime $date, int $months): DateTime {
        return new DateTime();
    }

    public function addOneMonthToAppointments(array $appointments): array {
        return array_map(function ($appointment) {
            $date = new DateTime($appointment['dataAgenda']);

            
            $date->add(new \DateInterval('P1M'));
            return $date->format('Y-m-d');
        }, $appointments);
    }
}