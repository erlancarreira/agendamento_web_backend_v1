<?php
namespace App\Interfaces;

interface DateAvailabilityInterface {
    public function getDateAvailable(array $date, array $appointments, int $quantity): ?array;
}