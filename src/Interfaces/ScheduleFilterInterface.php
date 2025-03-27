<?php 
namespace App\Interfaces;

interface ScheduleFilterInterface {
    public function filter(array $data, array $daysNotReturn): array;
}