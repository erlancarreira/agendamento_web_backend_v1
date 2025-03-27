<?php
namespace App\Interfaces;

use DateTime;

interface DateManipulatorInterface {
    public function addMonthsToDate(DateTime $date, int $months): DateTime;

    public function addOneMonthToAppointments(array $appointments): array;
}

