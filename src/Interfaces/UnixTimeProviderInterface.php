<?php 
namespace App\Interfaces;

interface UnixTimeProviderInterface {
    public function getUnixTimeFromDate(string $date);
}