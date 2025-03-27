<?php
namespace App\Interfaces;

interface AppointmentProcessorInterface {
    public function process(array $appointments): array;
}
