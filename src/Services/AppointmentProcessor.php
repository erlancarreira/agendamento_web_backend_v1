<?php
namespace App\Services;

use App\Interfaces\AppointmentProcessorInterface;
use App\Interfaces\DateConverterInterface;
use App\Interfaces\DateManipulatorInterface;
use DateTime;

class AppointmentProcessor implements AppointmentProcessorInterface {
    private $dateConverter;
    private $dateManipulator;

    public function __construct(DateConverterInterface $dateConverter, DateManipulatorInterface $dateManipulator) {
        $this->dateConverter   = $dateConverter;
        $this->dateManipulator = $dateManipulator;
    }

    public function process(array $appointments): array {

        return array_map(function ($appointment) {

            $dateSchedule = $this->dateConverter->convertDateFormat($appointment['dataAgenda']);
            $dateTime     = new DateTime($dateSchedule);
            $newDate      = $this->dateManipulator->addMonthsToDate($dateTime, 1);

            return $newDate->format('Y-m-d');

        }, $appointments);
    }
}