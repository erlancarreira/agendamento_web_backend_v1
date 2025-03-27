<?php
namespace App\Providers;

use App\Helpers\Helper;
use App\Interfaces\UnixTimeProviderInterface;

class UnixTimeProvider implements UnixTimeProviderInterface {
    public function getUnixTimeFromDate(string $date) {
        $dateFormated = Helper::formatDate($date);
        return $dateFormated->getTimestamp();
    }
}